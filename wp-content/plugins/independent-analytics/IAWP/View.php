<?php

namespace IAWP_SCOPED\IAWP;

use IAWP_SCOPED\IAWP\Models\Page_Author_Archive;
use IAWP_SCOPED\IAWP\Models\Page_Date_Archive;
use IAWP_SCOPED\IAWP\Models\Page_Home;
use IAWP_SCOPED\IAWP\Models\Page_Not_Found;
use IAWP_SCOPED\IAWP\Models\Page_Post_Type_Archive;
use IAWP_SCOPED\IAWP\Models\Page_Search;
use IAWP_SCOPED\IAWP\Models\Page_Singular;
use IAWP_SCOPED\IAWP\Models\Page_Term_Archive;
use IAWP_SCOPED\IAWP\Models\Visitor;
use IAWP_SCOPED\IAWP\Utils\String_Util;
use IAWP_SCOPED\IAWP\Utils\URL;
class View
{
    private $payload;
    private $referrer_url;
    private $visitor;
    private $campaign_fields;
    private $viewed_at;
    private $resource;
    private $session;
    /**
     * @param array $payload
     * @param string|null $referrer_url
     * @param Visitor $visitor
     * @param array $campaign_fields
     * @param \DateTime|null $viewed_at
     */
    public function __construct(array $payload, ?string $referrer_url, Visitor $visitor, array $campaign_fields, ?\DateTime $viewed_at = null)
    {
        $this->payload = $payload;
        $this->referrer_url = \trim($referrer_url);
        $this->visitor = $visitor;
        $this->campaign_fields = $campaign_fields;
        $this->viewed_at = $viewed_at instanceof \DateTime ? $viewed_at : new \DateTime();
        $this->resource = $this->fetch_or_create_resource();
        $this->session = $this->fetch_or_create_session();
        $view_id = $this->create_view();
        $this->link_with_previous_view($view_id);
        $this->set_sessions_initial_view($view_id);
        $this->set_sessions_final_view($view_id);
    }
    private function viewed_at() : string
    {
        return $this->viewed_at->format('Y-m-d H:i:s');
    }
    private function link_with_previous_view($view_id) : void
    {
        global $wpdb;
        $views_tables = Query::get_table_name(Query::VIEWS);
        $session = Query::query('sessions/get_session', ['session_id' => $this->session])->row();
        if (\is_null($session)) {
            return;
        }
        $final_view_id = $session->final_view_id;
        $initial_view_id = $session->initial_view_id;
        if (!\is_null($final_view_id)) {
            $wpdb->update($views_tables, ['next_view_id' => $view_id, 'next_viewed_at' => $this->viewed_at()], ['id' => $final_view_id]);
        } elseif (!\is_null($initial_view_id)) {
            $wpdb->update($views_tables, ['next_view_id' => $view_id, 'next_viewed_at' => $this->viewed_at()], ['id' => $initial_view_id]);
        }
    }
    private function set_sessions_initial_view(int $view_id)
    {
        global $wpdb;
        $sessions_table = Query::get_table_name(Query::SESSIONS);
        $wpdb->query($wpdb->prepare("UPDATE {$sessions_table} SET initial_view_id = %d WHERE session_id = %d AND initial_view_id IS NULL", $view_id, $this->session));
    }
    private function set_sessions_final_view(int $view_id)
    {
        global $wpdb;
        $sessions_table = Query::get_table_name(Query::SESSIONS);
        $wpdb->query($wpdb->prepare("\n                    UPDATE {$sessions_table} AS sessions\n                    SET\n                        sessions.final_view_id = %d,\n                        sessions.ended_at = %s\n                    WHERE sessions.session_id = %d AND sessions.initial_view_id IS NOT NULL AND sessions.initial_view_id != %d\n                ", $view_id, $this->viewed_at(), $this->session, $view_id));
    }
    private function create_view() : ?int
    {
        $viewed_at = $this->viewed_at();
        return Query::query('create_view', ['resource_id' => $this->resource, 'viewed_at' => $viewed_at, 'page' => $this->payload['page'], 'session_id' => $this->session])->last_inserted_id();
    }
    private function fetch_resource()
    {
        global $wpdb;
        $resources_table = Query::get_table_name(Query::RESOURCES);
        $query = '';
        $payload_copy = \array_merge($this->payload);
        unset($payload_copy['page']);
        switch ($payload_copy['resource']) {
            case 'singular':
                $query = $wpdb->prepare("SELECT * FROM {$resources_table} WHERE resource = %s AND singular_id = %d", $payload_copy['resource'], $payload_copy['singular_id']);
                break;
            case 'author_archive':
                $query = $wpdb->prepare("SELECT * FROM {$resources_table} WHERE resource = %s AND author_id = %d", $payload_copy['resource'], $payload_copy['author_id']);
                break;
            case 'date_archive':
                $query = $wpdb->prepare("SELECT * FROM {$resources_table} WHERE resource = %s AND date_archive = %s", $payload_copy['resource'], $payload_copy['date_archive']);
                break;
            case 'post_type_archive':
                $query = $wpdb->prepare("SELECT * FROM {$resources_table} WHERE resource = %s AND post_type = %s", $payload_copy['resource'], $payload_copy['post_type']);
                break;
            case 'term_archive':
                $query = $wpdb->prepare("SELECT * FROM {$resources_table} WHERE resource = %s AND term_id = %s", $payload_copy['resource'], $payload_copy['term_id']);
                break;
            case 'search':
                $query = $wpdb->prepare("SELECT * FROM {$resources_table} WHERE resource = %s AND search_query = %s", $payload_copy['resource'], $payload_copy['search_query']);
                break;
            case 'home':
                $query = $wpdb->prepare("SELECT * FROM {$resources_table} WHERE resource = %s ", $payload_copy['resource']);
                break;
            case '404':
                $query = $wpdb->prepare("SELECT * FROM {$resources_table} WHERE resource = %s AND not_found_url = %s", $payload_copy['resource'], $payload_copy['not_found_url']);
                break;
        }
        $resource = $wpdb->get_row($query);
        if (\is_null($resource)) {
            return null;
        }
        return $resource;
    }
    private function fetch_or_create_resource() : int
    {
        global $wpdb;
        $resources_table = Query::get_table_name(Query::RESOURCES);
        $resource = $this->fetch_resource();
        if (\is_null($resource)) {
            $payload_copy = \array_merge($this->payload);
            unset($payload_copy['page']);
            $wpdb->insert($resources_table, $payload_copy);
            $resource = $this->fetch_resource();
        }
        // Todo - This probably shouldn't happen here... A call should be make to the page and then the page should
        //  know if it's a page type that needs to be cached or not.
        switch ($resource->resource) {
            case 'singular':
                (new Page_Singular($resource))->update_cache();
                break;
            case 'author_archive':
                (new Page_Author_Archive($resource))->update_cache();
                break;
            case 'post_type_archive':
                (new Page_Post_Type_Archive($resource))->update_cache();
                break;
            case 'term_archive':
                (new Page_Term_Archive($resource))->update_cache();
                break;
            case 'home':
                (new Page_Home($resource))->update_cache();
                break;
            case 'date_archive':
                (new Page_Date_Archive($resource))->update_cache();
                break;
            case '404':
                (new Page_Not_Found($resource))->update_cache();
                break;
            case 'search':
                (new Page_Search($resource))->update_cache();
                break;
        }
        return $resource->id;
    }
    /**
     * @param string|null $referrer_url
     *
     * @return bool
     */
    private function from_internal_page(?string $referrer_url) : bool
    {
        return !empty($referrer_url) && String_Util::str_starts_with(\strtolower($referrer_url), \strtolower(\site_url()));
    }
    /**
     * @return int|null ID of the session that should be used for this view
     */
    private function fetch_or_create_session() : ?int
    {
        $from_external_page = !$this->from_internal_page($this->referrer_url);
        $session = Query::query('sessions/get_current_session', ['visitor_id' => $this->visitor->id()])->row();
        if (\is_null($session)) {
            return $this->create_session();
        }
        $same_referrer = $this->fetch_referrer() === $session->referrer_id;
        $same_resource = \intval($this->fetch_resource()->id) === $this->fetch_last_viewed_resource();
        $same_as_previous_view = $same_referrer && $same_resource;
        // The goal here is to prevent a page refresh from creating another session
        if ($from_external_page && !$same_as_previous_view) {
            return $this->create_session();
        }
        return $session->session_id;
    }
    /**
     * @return int ID of newly created session
     */
    public function create_session() : int
    {
        $created_at = $this->viewed_at();
        return Query::query('sessions/create_session', ['visitor_id' => $this->visitor->id(), 'referrer_id' => $this->fetch_or_create_referrer(), 'campaign_id' => $this->get_campaign(), 'created_at' => $created_at])->last_inserted_id();
    }
    /**
     * Fetch the last view, if any.
     *
     * @return int|null
     */
    public function fetch_last_viewed_resource() : ?int
    {
        global $wpdb;
        $views_table = Query::get_table_name(Query::VIEWS);
        $session = Query::query('sessions/get_current_session', ['visitor_id' => $this->visitor->id()])->row();
        if (\is_null($session)) {
            return null;
        }
        $view = $wpdb->get_row($wpdb->prepare("\n                SELECT * FROM {$views_table} WHERE session_id = %d ORDER BY viewed_at DESC LIMIT 1\n            ", $session->session_id));
        if (\is_null($view)) {
            return null;
        }
        return $view->resource_id;
    }
    /**
     * @return int|null ID of referrer, if any
     */
    public function fetch_referrer() : ?int
    {
        if (!isset($this->referrer_url) || \strlen($this->referrer_url) === 0) {
            return null;
        }
        $url = new URL($this->referrer_url);
        if (!$url->is_valid_url()) {
            return null;
        }
        if ($this->from_internal_page($this->referrer_url)) {
            return null;
        }
        $referrer = Query::query('get_referrer', ['domain' => $url->get_domain()])->row();
        if (\is_null($referrer)) {
            return null;
        }
        return $referrer->id;
    }
    private function fetch_or_create_referrer() : ?int
    {
        $referrer_id = $this->fetch_referrer();
        if (!\is_null($referrer_id)) {
            return $referrer_id;
        }
        $url = new URL($this->referrer_url);
        if (!$url->is_valid_url()) {
            return null;
        }
        if ($this->from_internal_page($this->referrer_url)) {
            return null;
        }
        Query::query('create_referrer', ['domain' => $url->get_domain()]);
        return $this->fetch_referrer();
    }
    private function get_campaign() : ?int
    {
        global $wpdb;
        $required_fields = ['utm_source', 'utm_medium', 'utm_campaign'];
        $valid = \true;
        foreach ($required_fields as $field) {
            if (!isset($this->campaign_fields[$field])) {
                $valid = \false;
            }
        }
        if (!$valid) {
            return null;
        }
        $campaigns_table = Query::get_table_name(Query::CAMPAIGNS);
        $campaign = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$campaigns_table} WHERE utm_source = %s AND utm_medium = %s AND utm_campaign = %s AND (utm_term = %s OR (%d = 0 AND utm_term IS NULL)) AND (utm_content = %s OR (%d = 0 AND utm_content IS NULL))", $this->campaign_fields['utm_source'], $this->campaign_fields['utm_medium'], $this->campaign_fields['utm_campaign'], $this->campaign_fields['utm_term'], isset($this->campaign_fields['utm_term']) ? 1 : 0, $this->campaign_fields['utm_content'], isset($this->campaign_fields['utm_content']) ? 1 : 0));
        if (!\is_null($campaign)) {
            return $campaign->campaign_id;
        }
        $wpdb->insert($campaigns_table, ['utm_source' => $this->campaign_fields['utm_source'], 'utm_medium' => $this->campaign_fields['utm_medium'], 'utm_campaign' => $this->campaign_fields['utm_campaign'], 'utm_term' => $this->campaign_fields['utm_term'], 'utm_content' => $this->campaign_fields['utm_content']]);
        $campaign = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$campaigns_table} WHERE utm_source = %s AND utm_medium = %s AND utm_campaign = %s AND (utm_term = %s OR (%d = 0 AND utm_term IS NULL)) AND (utm_content = %s OR (%d = 0 AND utm_content IS NULL))", $this->campaign_fields['utm_source'], $this->campaign_fields['utm_medium'], $this->campaign_fields['utm_campaign'], $this->campaign_fields['utm_term'], isset($this->campaign_fields['utm_term']) ? 1 : 0, $this->campaign_fields['utm_content'], isset($this->campaign_fields['utm_content']) ? 1 : 0));
        if (!\is_null($campaign)) {
            return $campaign->campaign_id;
        }
        return null;
    }
}
