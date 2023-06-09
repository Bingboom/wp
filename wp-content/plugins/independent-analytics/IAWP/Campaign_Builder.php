<?php

namespace IAWP_SCOPED\IAWP;

use IAWP_SCOPED\IAWP\Utils\Singleton;
use IAWP_SCOPED\IAWP\Utils\String_Util;
use IAWP_SCOPED\IAWP\Utils\URL;
use IAWP_SCOPED\League\Uri\Uri;
class Campaign_Builder
{
    use Singleton;
    public function __construct()
    {
    }
    private function time_ago($time)
    {
        $time_difference = \time() - $time;
        if ($time_difference < 1) {
            return 'less than 1 second ago';
        }
        $condition = [12 * 30 * 24 * 60 * 60 => 'year', 30 * 24 * 60 * 60 => 'month', 24 * 60 * 60 => 'day', 60 * 60 => 'hour', 60 => 'minute', 1 => 'second'];
        foreach ($condition as $secs => $str) {
            $d = $time_difference / $secs;
            if ($d >= 1) {
                $t = \round($d);
                return $t . ' ' . $str . ($t > 1 ? 's' : '') . ' ago';
            }
        }
    }
    private function get_previously_created_campaigns()
    {
        global $wpdb;
        $campaign_urls_table = Query::get_table_name(Query::CAMPAIGN_URLS);
        $results = $wpdb->get_results("\n            SELECT * FROM {$campaign_urls_table} ORDER BY created_at DESC LIMIT 100\n        ");
        return \array_map(function ($result) {
            $created_at = new \DateTime($result->created_at);
            $time_ago = $this->time_ago($created_at->getTimestamp());
            return ['result' => \json_encode((array) $result), 'created_at' => $time_ago, 'url' => $this->build_url($result->path, $result->utm_source, $result->utm_medium, $result->utm_campaign, $result->utm_term, $result->utm_content)];
        }, $results);
    }
    public function render_campaign_builder()
    {
        echo \IAWP_SCOPED\iawp()->templates()->render('campaign_builder', ['campaigns' => $this->get_previously_created_campaigns()]);
    }
    public function create_campaign($path, $source, $medium, $campaign, $term, $content)
    {
        global $wpdb;
        $has_errors = \false;
        $path = \strlen($path) > 0 ? $path : '';
        $path_error = null;
        $source_error = null;
        $medium_error = null;
        $campaign_error = null;
        $term = \strlen($term) > 0 ? $term : null;
        $content = \strlen($content) > 0 ? $content : null;
        $url = new URL(\site_url() . $path);
        if (!$url->is_valid_url()) {
            $has_errors = \true;
            $path_error = 'path invalid';
        }
        if (\strlen($source) === 0) {
            $has_errors = \true;
            $source_error = 'Source is required';
        }
        if (\strlen($medium) === 0) {
            $has_errors = \true;
            $medium_error = 'Medium is required';
        }
        if (\strlen($campaign) === 0) {
            $has_errors = \true;
            $campaign_error = 'Campaign is required';
        }
        if ($has_errors) {
            return \IAWP_SCOPED\iawp()->templates()->render('campaign_builder', ['path' => $path, 'path_error' => $path_error, 'utm_source' => $source, 'utm_source_error' => $source_error, 'utm_medium' => $medium, 'utm_medium_error' => $medium_error, 'utm_campaign' => $campaign, 'utm_campaign_error' => $campaign_error, 'utm_term' => $term, 'utm_content' => $content, 'campaigns' => $this->get_previously_created_campaigns()]);
        }
        $campaign_urls_table = Query::get_table_name(Query::CAMPAIGN_URLS);
        $wpdb->insert($campaign_urls_table, ['path' => $path, 'utm_source' => $source, 'utm_medium' => $medium, 'utm_campaign' => $campaign, 'utm_term' => $term, 'utm_content' => $content, 'created_at' => (new \DateTime())->format('Y-m-d H:i:s')]);
        $url = $this->build_url($path, $source, $medium, $campaign, $term, $content);
        return \IAWP_SCOPED\iawp()->templates()->render('campaign_builder', ['path' => $path, 'utm_source' => $source, 'utm_medium' => $medium, 'utm_campaign' => $campaign, 'utm_term' => $term, 'utm_content' => $content, 'new_campaign_url' => $url, 'campaigns' => $this->get_previously_created_campaigns()]);
    }
    public function build_url($path, $source, $medium, $campaign, $term = null, $content = null) : string
    {
        $path = String_Util::str_starts_with($path, '/') ? \substr($path, 1) : $path;
        $uri = Uri::createFromString(\trailingslashit(\site_url()) . $path);
        $existing_query = $uri->getQuery();
        \parse_str($existing_query, $existing_query);
        $existing_query['utm_source'] = $source;
        $existing_query['utm_medium'] = $medium;
        $existing_query['utm_campaign'] = $campaign;
        if (isset($term)) {
            $existing_query['utm_term'] = $term;
        }
        if (isset($content)) {
            $existing_query['utm_content'] = $content;
        }
        return $uri->withQuery(\http_build_query($existing_query));
    }
}
