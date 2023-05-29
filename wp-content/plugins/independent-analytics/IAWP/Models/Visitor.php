<?php

namespace IAWP_SCOPED\IAWP\Models;

use IAWP_SCOPED\IAWP\Geo_Database;
use IAWP_SCOPED\IAWP\Query;
use IAWP_SCOPED\IAWP\Utils\Salt;
/**
 * How to use:
 *
 * Example IP from the Netherlands
 * $visitor = new Visitor('92.111.145.208', 'some ua string');
 *
 * Example IP from the United States
 * $visitor = new Visitor('98.111.145.208', 'some ua string');
 *
 * Access visitor token
 * $visitor->id();
 *
 * Access geo data
 * $visitor->country_code()
 * $visitor->city()
 * $visitor->subdivision()
 * $visitor->country()
 * $visitor->continent()
 */
class Visitor
{
    private $id;
    private $geo;
    /**
     * New instances should be created with a string ip address
     *
     * @param string $ip
     * @param string $user_agent
     */
    public function __construct(string $ip, string $user_agent)
    {
        $geo_database = new Geo_Database();
        $this->id = self::calculate_id($ip, $user_agent);
        $this->geo = $geo_database->ip_to_geo($ip);
    }
    /**
     * Get the id for the most recent view for a visitor
     *
     * @return int|null
     */
    public function most_recent_view_id() : ?int
    {
        global $wpdb;
        $views_table = Query::get_table_name(Query::VIEWS);
        $sessions_table = Query::get_table_name(Query::SESSIONS);
        $id = $wpdb->get_var($wpdb->prepare("\n                SELECT views.id as id\n                FROM {$views_table} AS views\n                         LEFT JOIN {$sessions_table} AS sessions ON sessions.session_id = views.session_id\n                WHERE sessions.visitor_id = %s\n                ORDER BY views.viewed_at DESC\n                LIMIT 1\n                ", $this->id()));
        if (\is_null($id)) {
            return null;
        }
        return \intval($id);
    }
    public function upsert()
    {
        Query::query('create_visitor', ['visitor_id' => $this->id(), 'country_code' => $this->country_code(), 'city' => $this->city(), 'subdivision' => $this->subdivision(), 'country' => $this->country(), 'continent' => $this->continent()]);
    }
    /**
     * @param string $ip
     * @param string $user_agent
     * @return string
     */
    private function calculate_id(string $ip, string $user_agent) : string
    {
        $salt = Salt::visitor_token_salt();
        $result = $salt . $ip . $user_agent;
        return \md5($result);
    }
    /**
     * Return the database id for a visitor
     *
     * @return string
     */
    public function id() : string
    {
        return $this->id;
    }
    /**
     * Return an ISO country code
     *
     * @return string|null
     */
    public function country_code() : ?string
    {
        if (isset($this->geo['country']['iso_code'])) {
            return $this->geo['country']['iso_code'];
        } else {
            return null;
        }
    }
    /**
     * Return an English city name
     *
     * @return string|null
     */
    public function city() : ?string
    {
        if (isset($this->geo['city']['names']['en'])) {
            return $this->geo['city']['names']['en'];
        } else {
            return null;
        }
    }
    /**
     * Return an English subdivision name
     *
     * @return string|null
     */
    public function subdivision() : ?string
    {
        if (isset($this->geo['subdivisions'][0]['names']['en'])) {
            return $this->geo['subdivisions'][0]['names']['en'];
        } else {
            return null;
        }
    }
    /**
     * @return string|null
     */
    public function country() : ?string
    {
        if (isset($this->geo['country']['names']['en'])) {
            return $this->geo['country']['names']['en'];
        } else {
            return null;
        }
    }
    /**
     * @return string|null
     */
    public function continent() : ?string
    {
        if (isset($this->geo['continent']['names']['en'])) {
            return $this->geo['continent']['names']['en'];
        } else {
            return null;
        }
    }
}
