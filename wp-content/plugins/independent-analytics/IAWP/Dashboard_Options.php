<?php

namespace IAWP_SCOPED\IAWP;

use DateTime;
use IAWP_SCOPED\IAWP\Date_Range\Date_Range;
use IAWP_SCOPED\IAWP\Date_Range\Exact_Date_Range;
use IAWP_SCOPED\IAWP\Date_Range\Relative_Date_Range;
use IAWP_SCOPED\IAWP\Utils\Timezone;
use Throwable;
/**
 * Dashboards support various options via the search query string portion of the URL.
 *
 * The Dashboard_Options class give you an interface for fetching any set values or falling back
 * to a default value as needed.
 */
class Dashboard_Options
{
    public function __construct()
    {
    }
    private function get_query_value($key)
    {
        $value = $_GET[$key] ?? null;
        if ($value === null) {
            return null;
        }
        return \sanitize_text_field($value);
    }
    private function has_exact_range()
    {
        return $this->get_query_value('start') !== null && $this->get_query_value('end') !== null;
    }
    public function start()
    {
        $start = $this->get_query_value('start');
        if (!$this->has_exact_range()) {
            return null;
        }
        return $start;
    }
    public function end()
    {
        $end = $this->get_query_value('end');
        if (!$this->has_exact_range()) {
            return null;
        }
        return $end;
    }
    /**
     * Prefer exact range to relative range if both are provided
     */
    public function relative_range_id()
    {
        $relative_range_id = $this->get_query_value('relative_range');
        if (!$this->has_exact_range() && $relative_range_id === null) {
            return 'LAST_THIRTY';
        } elseif ($this->has_exact_range()) {
            return null;
        } elseif (Relative_Date_Range::is_valid_range($relative_range_id) === \false) {
            return 'LAST_THIRTY';
        }
        return $relative_range_id;
    }
    /**
     * @return Date_Range
     */
    public function get_date_range() : Date_Range
    {
        if ($this->has_exact_range()) {
            try {
                $start = new DateTime($this->start(), Timezone::wordpress_site_timezone());
                $end = new DateTime($this->end(), Timezone::wordpress_site_timezone());
                return new Exact_Date_Range($start, $end);
            } catch (Throwable $e) {
                // Do nothing and fall back to default relative date range
            }
        }
        return new Relative_Date_Range($this->relative_range_id());
    }
    public function columns()
    {
        $value = $_GET['cols'] ?? null;
        if ($value === null) {
            return null;
        }
        $columns = \explode(',', $value);
        \array_map(function ($column) {
            return \sanitize_text_field($column);
        }, $columns);
        return $columns;
    }
    public function filters()
    {
        $value = $_GET['filters'] ?? null;
        if ($value === null) {
            return [];
        } else {
            $value = \base64_decode($value);
            $filters = \json_decode($value);
            $sanitized_filters = [];
            foreach ($filters as $filter) {
                $sanitized_filter = [];
                foreach ($filter as $key => $value) {
                    $sanitized_filter[$key] = \sanitize_text_field($value);
                }
                $sanitized_filters[] = $sanitized_filter;
            }
        }
        return $sanitized_filters;
    }
    public function sort_by()
    {
        return $this->get_query_value('sort_by') ?? 'visitors';
    }
    public function sort_direction()
    {
        return $this->get_query_value('sort_direction') ?? 'desc';
    }
}
