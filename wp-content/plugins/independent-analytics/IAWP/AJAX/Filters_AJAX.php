<?php

namespace IAWP_SCOPED\IAWP\AJAX;

use DateTime;
use IAWP_SCOPED\IAWP\Statistics\Campaign_Statistics;
use IAWP_SCOPED\IAWP\Statistics\Geo_Statistics;
use IAWP_SCOPED\IAWP\Statistics\Page_Statistics;
use IAWP_SCOPED\IAWP\Statistics\Referrer_Statistics;
use Throwable;
use IAWP_SCOPED\IAWP\Chart;
use IAWP_SCOPED\IAWP\Chart_Geo;
use IAWP_SCOPED\IAWP\Date_Range\Date_Range;
use IAWP_SCOPED\IAWP\Date_Range\Exact_Date_Range;
use IAWP_SCOPED\IAWP\Date_Range\Relative_Date_Range;
use IAWP_SCOPED\IAWP\Filters;
use IAWP_SCOPED\IAWP\Models\Campaign;
use IAWP_SCOPED\IAWP\Models\Geo;
use IAWP_SCOPED\IAWP\Models\Referrer;
use IAWP_SCOPED\IAWP\Queries\Campaigns;
use IAWP_SCOPED\IAWP\Queries\City_Statistics;
use IAWP_SCOPED\IAWP\Queries\Country_Statistics;
use IAWP_SCOPED\IAWP\Queries\Referrers;
use IAWP_SCOPED\IAWP\Queries\Resources;
use IAWP_SCOPED\IAWP\Quick_Stats;
use IAWP_SCOPED\IAWP\Tables\Table_Campaigns;
use IAWP_SCOPED\IAWP\Tables\Table_Geo;
use IAWP_SCOPED\IAWP\Tables\Table_Referrers;
use IAWP_SCOPED\IAWP\Tables\Table_Pages;
use IAWP_SCOPED\IAWP\Utils\Timezone;
class Filters_AJAX extends AJAX
{
    protected function action_name() : string
    {
        return 'iawp_filter';
    }
    protected function action_required_fields() : array
    {
        return ['table_type', 'columns'];
    }
    /**
     * Get the date range for the filter request
     *
     * The date info can be supplied in one of two ways.
     *
     * The first is to provide a relative_range_id which is converted into start, end, and label.
     *
     * The second is to provide explicit start and end fields which will be used as is.
     *
     * @return Date_Range
     */
    private function get_date_range() : Date_Range
    {
        $relative_range_id = $this->get_field('relative_range_id');
        $start_timestamp = $this->get_field('exact_start');
        $end_timestamp = $this->get_field('exact_end');
        if (!\is_null($start_timestamp) && !\is_null($end_timestamp)) {
            try {
                $start = new DateTime($start_timestamp, Timezone::wordpress_site_timezone());
                $end = new DateTime($end_timestamp, Timezone::wordpress_site_timezone());
                return new Exact_Date_Range($start, $end);
            } catch (Throwable $e) {
                // Do nothing and fall back to default relative date range
            }
        }
        return new Relative_Date_Range($relative_range_id);
    }
    protected function action_callback() : void
    {
        $filters = $this->get_field('filters') ?? [];
        $columns = $this->get_field('columns');
        $table_type = $this->get_field('table_type');
        $is_pages_table = $table_type === 'views';
        $is_referrers_table = $table_type === 'referrers';
        $is_geo_table = $table_type === 'geo';
        $is_campaigns_table = $table_type === 'campaigns';
        $date_range = $this->get_date_range();
        $sort_by = $this->get_field('sort_by') ?? 'visitors';
        $sort_direction = $this->get_field('sort_direction') ?? 'desc';
        $page = $this->get_field('page') ?? 1;
        $unfiltered_statistics = null;
        if (!$is_pages_table && !$is_referrers_table && !$is_geo_table && !$is_campaigns_table) {
            return;
        }
        if ($is_pages_table) {
            $table = new Table_Pages($columns);
            $resources = new Resources($date_range);
            $rows = $resources->fetch();
            $viewed_resource_ids = $this->get_viewed_resource_ids($rows);
            $statistics = new Page_Statistics($date_range, $viewed_resource_ids);
            if (!empty($filters)) {
                $unfiltered_statistics = $statistics;
                $rows = $this->get_filtered_rows($rows, $filters);
                $viewed_resource_ids = $this->get_viewed_resource_ids($rows);
                $statistics = new Page_Statistics($date_range, $viewed_resource_ids);
            }
            $chart = new Chart($statistics, $date_range->label());
        } elseif ($is_referrers_table) {
            $table = new Table_Referrers($columns);
            $referrers = new Referrers($date_range);
            $rows = $referrers->fetch();
            $referrer_ids = Referrer::referrer_ids($rows);
            $statistics = new Referrer_Statistics($date_range, $referrer_ids);
            if (!empty($filters)) {
                $unfiltered_statistics = $statistics;
                $rows = $this->get_filtered_rows($rows, $filters);
                $referrer_ids = Referrer::referrer_ids($rows);
                $statistics = new Referrer_Statistics($date_range, $referrer_ids);
            }
            $chart = new Chart($statistics, $date_range->label());
        } elseif ($is_geo_table) {
            $table = new Table_Geo($columns);
            $geos = new City_Statistics($date_range);
            $rows = $geos->fetch();
            $geo_ids = Geo::geo_ids($rows);
            $statistics = new Geo_Statistics($date_range, $geo_ids);
            if (!empty($filters)) {
                $unfiltered_statistics = $statistics;
                $rows = $this->get_filtered_rows($rows, $filters);
                $geo_ids = Geo::geo_ids($rows);
                $statistics = new Geo_Statistics($date_range, $geo_ids);
            }
            $country_statistics = new Country_Statistics($date_range);
            $stats = $this->get_filtered_rows($country_statistics->fetch(), $filters);
            $chart = new Chart_Geo($stats, $date_range->label());
        } elseif ($is_campaigns_table) {
            $table = new Table_Campaigns($columns);
            $rows = (new Campaigns($date_range))->fetch();
            $campaign_ids = Campaign::campaigns_to_ids($rows);
            $statistics = new Campaign_Statistics($date_range, $campaign_ids);
            if (!empty($filters)) {
                $unfiltered_statistics = $statistics;
                $rows = $this->get_filtered_rows($rows, $filters);
                $campaign_ids = Campaign::campaigns_to_ids($rows);
                $statistics = new Campaign_Statistics($date_range, $campaign_ids);
            }
            $chart = new Chart($statistics, $date_range->label());
        } else {
            return;
        }
        $rows = $this->sort_rows($rows, $sort_direction, $sort_by);
        $page_size = \IAWP_SCOPED\iawp()->pagination_page_size();
        $paged_rows = \array_slice($rows, 0, $page_size * $page);
        $is_last_page = \count($rows) === \count($paged_rows);
        $table->set_statistics($statistics);
        $quick_stats = new Quick_Stats($statistics, $unfiltered_statistics);
        $html = $table->get_row_markup($paged_rows);
        \wp_send_json_success(['rows' => $html, 'totalRowCount' => \count($rows), 'chart' => $chart->get_html(), 'stats' => $quick_stats->get_html(), 'label' => $date_range->label(), 'isLastPage' => $is_last_page]);
    }
    protected function get_viewed_resource_ids($rows)
    {
        return \array_map(function ($resource) {
            return $resource->id();
        }, $rows);
    }
    protected function get_filtered_rows($rows, $filters)
    {
        $filterer = new Filters();
        return $filterer->fitler_rows($rows, $filters);
    }
    protected function sort_rows($rows, $sort_direction, $sort_by)
    {
        \usort($rows, function ($a, $b) use($sort_direction, $sort_by) {
            if (\method_exists($a, $sort_by) && \method_exists($b, $sort_by)) {
                $a_val = $a->{$sort_by}();
                $b_val = $b->{$sort_by}();
                $switch = $sort_direction === 'asc' ? 1 : -1;
                // Null and empty values at bottom in asc and top in desc
                $a_empty = \is_null($a_val) || \strlen($a_val) === 0;
                $b_empty = \is_null($b_val) || \strlen($b_val) === 0;
                if ($a_empty && !$b_empty) {
                    return 1;
                } elseif ($b_empty && !$a_empty) {
                    return -1;
                } elseif ($a_empty && $b_empty) {
                    return 0;
                }
                // Numbers below letters
                $a_num = \is_numeric($a_val);
                $b_num = \is_numeric($b_val);
                if ($a_num && !$b_num) {
                    return $switch;
                } elseif ($b_num && !$a_num) {
                    return $switch * -1;
                }
                return (\strtolower($a_val) <=> \strtolower($b_val)) * $switch;
            } else {
                return 0;
            }
        });
        return $rows;
    }
}
