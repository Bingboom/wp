<?php

namespace IAWP_SCOPED\IAWP\Statistics;

use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use IAWP_SCOPED\IAWP\Date_Range\Date_Range;
use IAWP_SCOPED\IAWP\Illuminate_Builder;
use IAWP_SCOPED\IAWP\Query;
use IAWP_SCOPED\IAWP\Utils\Timezone;
use IAWP_SCOPED\Illuminate\Database\Query\Builder;
use IAWP_SCOPED\Illuminate\Database\Query\JoinClause;
abstract class Statistics
{
    protected $date_range;
    protected $allowed_ids;
    private $views;
    private $prev_period_views;
    private $daily_views;
    private $visitors;
    private $prev_period_visitors;
    private $daily_visitors;
    private $sessions;
    private $prev_period_sessions;
    private $daily_sessions;
    private $average_session_duration;
    private $prev_period_average_session_duration;
    private $daily_average_session_durations;
    private $woocommerce_orders;
    private $prev_woocommerce_orders;
    private $woocommerce_net_sales;
    private $prev_woocommerce_net_sales;
    private $daily_woocommerce_orders;
    private $daily_woocommerce_net_sales;
    /**
     * @param Date_Range $date_range
     * @param int[] $allowed_ids
     */
    public function __construct(Date_Range $date_range, array $allowed_ids = null)
    {
        $this->date_range = $date_range;
        $this->allowed_ids = $allowed_ids;
        $this->fetch();
    }
    /**
     * Statistics could be filtered down to a set of pages, a set of referrers, a set of geos, or
     * a set of campaigns. This method allows subclasses to define what identifier column they
     * want to use when filtering by id.
     *
     * @return string
     */
    protected abstract function allowed_in_id_column() : string;
    /**
     * Statistics may wish to keep rows that have a null value in the id column. An example of
     * this would be for referrers where a null referrer_id is a special type of referrer, a direct
     * referrer.
     *
     * @return bool
     */
    protected function allow_null_in_id_column() : bool
    {
        return \false;
    }
    /**
     * Statistics can require that a column exists in order to be included. As an example, geos
     * requires visitors.country_code and campaigns requires sessions.campaign_id
     *
     * @return ?string
     */
    protected function required_column() : ?string
    {
        return null;
    }
    public function query(string $start, string $end, bool $as_daily_statistics)
    {
        $utc_offset = Timezone::utc_offset();
        $wordpress_site_offset = Timezone::wordpress_site_offset();
        $sessions_table = Query::get_table_name(Query::SESSIONS);
        $views_table = Query::get_table_name(Query::VIEWS);
        $visitors_table = Query::get_table_name(Query::VISITORS);
        $wc_orders_table = Query::get_table_name(Query::WC_ORDERS);
        $session_statistics = Illuminate_Builder::get_builder();
        $session_statistics->select('sessions.session_id')->selectRaw('COUNT(DISTINCT views.id) AS views')->selectRaw('COUNT(DISTINCT wc_orders.order_id) AS orders')->selectRaw('IFNULL(CAST(SUM(wc_orders.total) AS UNSIGNED), 0) AS gross_sales')->selectRaw('IFNULL(CAST(SUM(wc_orders.total_refunded) AS UNSIGNED), 0) AS total_refunded')->selectRaw('IFNULL(CAST(SUM(wc_orders.total_refunds) AS UNSIGNED), 0) AS total_refunds')->selectRaw('IFNULL(CAST(SUM(wc_orders.total - wc_orders.total_refunded) AS UNSIGNED), 0) AS net_sales')->from("{$sessions_table} AS sessions")->join("{$views_table} AS views", function (JoinClause $join) {
            $join->on('sessions.session_id', '=', 'views.session_id');
        })->leftJoin("{$wc_orders_table} AS wc_orders", function (JoinClause $join) {
            $join->on('views.id', '=', 'wc_orders.view_id')->whereIn('wc_orders.status', ['wc-completed', 'completed', 'wc-processing', 'processing', 'wc-refunded', 'refunded']);
        })->whereBetween('sessions.created_at', [$start, $end])->whereBetween('views.viewed_at', [$start, $end])->groupBy('sessions.session_id')->when(!\is_null($this->allowed_ids), function (Builder $query) {
            if ($this->allow_null_in_id_column()) {
                $query->where(function (Builder $query) {
                    $query->whereNull($this->allowed_in_id_column())->orWhereIn($this->allowed_in_id_column(), $this->allowed_ids);
                });
            } else {
                $query->whereIn($this->allowed_in_id_column(), $this->allowed_ids);
            }
        })->when($this->required_column() === 'visitors.country_code', function (Builder $query) use($visitors_table) {
            $query->join("{$visitors_table} as visitors", function (JoinClause $join) {
                $join->on('visitors.visitor_id', '=', 'sessions.visitor_id');
            });
        })->when(!\is_null($this->required_column()), function (Builder $query) {
            $query->whereNotNull($this->required_column());
        });
        $statistics = Illuminate_Builder::get_builder();
        $statistics->selectRaw('IFNULL(CAST(SUM(session_statistics.views) AS UNSIGNED), 0) AS views')->selectRaw('CAST(COUNT(DISTINCT sessions.visitor_id) AS UNSIGNED) AS visitors')->selectRaw('CAST(COUNT(DISTINCT sessions.session_id) AS UNSIGNED) AS sessions')->selectRaw('IFNULL(CAST(AVG(TIMESTAMPDIFF(SECOND, sessions.created_at, sessions.ended_at)) AS UNSIGNED), 0) AS average_session_duration')->selectRaw('IFNULL(CAST(SUM(session_statistics.orders) AS UNSIGNED), 0) AS wc_orders')->selectRaw('IFNULL(CAST(SUM(session_statistics.gross_sales) AS UNSIGNED), 0) AS wc_gross_sales')->selectRaw('IFNULL(CAST(SUM(session_statistics.total_refunds) AS UNSIGNED), 0) AS wc_refunds')->selectRaw('IFNULL(CAST(SUM(session_statistics.total_refunded) AS UNSIGNED), 0) AS wc_refunded_amount')->selectRaw('IFNULL(CAST(SUM(session_statistics.net_sales) AS UNSIGNED), 0) AS wc_net_sales')->from("{$sessions_table} AS sessions")->joinSub($session_statistics, 'session_statistics', function (JoinClause $join) {
            $join->on('sessions.session_id', '=', 'session_statistics.session_id');
        })->whereBetween('sessions.created_at', [$start, $end])->when($as_daily_statistics, function (Builder $query) use($utc_offset, $wordpress_site_offset) {
            $query->selectRaw("DATE(CONVERT_TZ(sessions.created_at, '{$utc_offset}', '{$wordpress_site_offset}')) AS date");
            $query->groupByRaw("DATE(CONVERT_TZ(sessions.created_at, '{$utc_offset}', '{$wordpress_site_offset}'))");
        });
        if ($as_daily_statistics) {
            return $statistics->get()->all();
        } else {
            return $statistics->get()->first();
        }
    }
    public function fetch()
    {
        $statistics_by_day = $this->query($this->date_range->iso_start(), $this->date_range->iso_end(), \true);
        $statistics = $this->query($this->date_range->iso_start(), $this->date_range->iso_end(), \false);
        $previous_period_statistics = $this->query($this->date_range->previous_period_iso_start(), $this->date_range->previous_period_iso_end(), \false);
        // Views
        $this->views = $statistics->views;
        $this->prev_period_views = $previous_period_statistics->views;
        $this->daily_views = $this->fill_in_partial_day_range($statistics_by_day, 'views');
        // Visitors
        $this->visitors = $statistics->visitors;
        $this->prev_period_visitors = $previous_period_statistics->visitors;
        $this->daily_visitors = $this->fill_in_partial_day_range($statistics_by_day, 'visitors');
        // Sessions
        $this->sessions = $statistics->sessions;
        $this->prev_period_sessions = $previous_period_statistics->sessions;
        $this->daily_sessions = $this->fill_in_partial_day_range($statistics_by_day, 'sessions');
        // Average Session Duration
        $this->average_session_duration = $statistics->average_session_duration;
        $this->prev_period_average_session_duration = $previous_period_statistics->average_session_duration;
        $this->daily_average_session_durations = $this->fill_in_partial_day_range($statistics_by_day, 'average_session_duration');
        if ($this->average_session_duration === null) {
            $this->average_session_duration = 0;
        }
        if ($this->prev_period_average_session_duration === null) {
            $this->prev_period_average_session_duration = 0;
        }
        // WooCommerce Orders
        $this->woocommerce_orders = $statistics->wc_orders;
        $this->prev_woocommerce_orders = $previous_period_statistics->wc_orders;
        $this->daily_woocommerce_orders = $this->fill_in_partial_day_range($statistics_by_day, 'wc_orders');
        // WooCommerce Net Sales
        $this->woocommerce_net_sales = $statistics->wc_gross_sales - $statistics->wc_refunded_amount;
        $this->prev_woocommerce_net_sales = $previous_period_statistics->wc_gross_sales - $previous_period_statistics->wc_refunded_amount;
        $this->daily_woocommerce_net_sales = $this->fill_in_partial_day_range($statistics_by_day, 'wc_net_sales');
    }
    public function views()
    {
        return $this->views;
    }
    public function prev_period_views()
    {
        return $this->prev_period_views;
    }
    public function daily_views()
    {
        return $this->daily_views;
    }
    public function views_percentage_growth()
    {
        return $this->percentage_growth($this->views(), $this->prev_period_views());
    }
    public function visitors()
    {
        return $this->visitors;
    }
    public function visitors_percentage_growth()
    {
        return $this->percentage_growth($this->visitors(), $this->prev_period_visitors());
    }
    public function prev_period_visitors()
    {
        return $this->prev_period_visitors;
    }
    public function daily_visitors()
    {
        return $this->daily_visitors;
    }
    public function sessions()
    {
        return $this->sessions;
    }
    public function sessions_percentage_growth()
    {
        return $this->percentage_growth($this->sessions(), $this->prev_period_sessions());
    }
    public function prev_period_sessions()
    {
        return $this->prev_period_sessions;
    }
    public function daily_sessions()
    {
        return $this->daily_sessions;
    }
    public function average_session_duration()
    {
        return $this->average_session_duration;
    }
    public function average_session_duration_percentage_growth()
    {
        return $this->percentage_growth($this->average_session_duration(), $this->prev_period_average_session_duration());
    }
    public function prev_period_average_session_duration()
    {
        return $this->prev_period_average_session_duration;
    }
    public function daily_average_session_durations()
    {
        return $this->daily_average_session_durations;
    }
    public function woocommerce_orders() : int
    {
        return $this->woocommerce_orders;
    }
    public function prev_woocommerce_orders() : int
    {
        return $this->prev_woocommerce_orders;
    }
    public function woocommerce_orders_percentage_growth() : float
    {
        return $this->percentage_growth($this->woocommerce_orders(), $this->prev_woocommerce_orders());
    }
    public function woocommerce_net_sales() : float
    {
        return $this->woocommerce_net_sales;
    }
    public function prev_woocommerce_net_sales() : float
    {
        return $this->prev_woocommerce_net_sales;
    }
    public function woocommerce_net_sales_percentage_growth() : float
    {
        return $this->percentage_growth((int) $this->woocommerce_net_sales(), (int) $this->prev_woocommerce_net_sales());
    }
    public function daily_woocommerce_orders() : array
    {
        return $this->daily_woocommerce_orders;
    }
    public function daily_woocommerce_net_sales() : array
    {
        return $this->daily_woocommerce_net_sales;
    }
    private function percentage_growth(int $current_period, int $previous_period) : float
    {
        if ($current_period === 0 && $previous_period !== 0) {
            return -100;
        } elseif ($current_period === 0 || $previous_period === 0) {
            return 0;
        }
        $growth = ($current_period / $previous_period - 1) * 100;
        return \round($growth, 0);
    }
    /**
     * @param array $partial_day_range
     * @param string $field
     *
     * @return array
     */
    private function fill_in_partial_day_range(array $partial_day_range, string $field) : array
    {
        $user_timezone = Timezone::wordpress_site_timezone();
        $start = clone $this->date_range->start();
        $end = clone $this->date_range->end();
        $start->setTimezone($user_timezone);
        $end->setTimezone($user_timezone);
        $interval = new DateInterval('P1D');
        $date_range = new DatePeriod($start, $interval, $end);
        $filled_in_data = [];
        foreach ($date_range as $date) {
            $stat = $this->get_statistic_for_date($partial_day_range, $date, $field);
            $filled_in_data[] = [$date, $stat];
        }
        return $filled_in_data;
    }
    /**
     * @param array $partial_day_range
     * @param DateTime $datetime_to_match
     * @param string $field
     *
     * @return int Defaults to 0
     */
    private function get_statistic_for_date(array $partial_day_range, DateTime $datetime_to_match, string $field) : ?int
    {
        $user_timezone = Timezone::wordpress_site_timezone();
        $default_value = 0;
        foreach ($partial_day_range as $day) {
            $date = $day->date;
            $stat = $day->{$field};
            try {
                $datetime = new DateTime($date, $user_timezone);
            } catch (Exception $e) {
                return $default_value;
            }
            // Intentionally using non-strict equality to see if two distinct DateTime objects represent the same time
            if ($datetime == $datetime_to_match) {
                return $stat;
            }
        }
        return $default_value;
    }
}
