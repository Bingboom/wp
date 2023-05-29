<?php

namespace IAWP_SCOPED\IAWP;

use IAWP_SCOPED\IAWP\Statistics\Statistics;
use IAWP_SCOPED\IAWP\Utils\Currency;
use IAWP_SCOPED\IAWP\Utils\Number_Formatter;
class Quick_Stats
{
    private $statistics;
    private $filtered_statistics;
    private $preview;
    /**
     * @param Statistics $statistics
     * @param Statistics|null $unfiltered_statistics
     * @param bool $preview
     */
    public function __construct(Statistics $statistics, ?Statistics $unfiltered_statistics, bool $preview = \false)
    {
        $this->preview = $preview;
        if (\is_null($unfiltered_statistics)) {
            $this->statistics = $statistics;
        } else {
            $this->statistics = $unfiltered_statistics;
            $this->filtered_statistics = $statistics;
        }
    }
    /**
     * @return bool
     */
    private function is_preview() : bool
    {
        return $this->preview;
    }
    /**
     * @return bool
     */
    private function is_full_view() : bool
    {
        return !$this->is_preview();
    }
    public function get_html()
    {
        $is_filtered = !\is_null($this->filtered_statistics);
        $statistics = $is_filtered ? $this->filtered_statistics : $this->statistics;
        $stats = [['title' => \__('Visitors', 'iawp'), 'class' => 'visitors', 'count' => \number_format($statistics->visitors()), 'growth' => $statistics->visitors_percentage_growth(), 'unfiltered' => \number_format($this->statistics->visitors())], ['title' => \__('Views', 'iawp'), 'class' => 'views', 'count' => \number_format($statistics->views()), 'growth' => $statistics->views_percentage_growth(), 'unfiltered' => \number_format($this->statistics->views())]];
        if ($this->is_full_view()) {
            $stats[] = ['title' => \__('Sessions', 'iawp'), 'class' => 'sessions', 'count' => \number_format($statistics->sessions()), 'growth' => $statistics->sessions_percentage_growth(), 'unfiltered' => \number_format($this->statistics->sessions())];
            $stats[] = ['title' => \__('Session Duration', 'iawp'), 'class' => 'average-session-duration', 'count' => Number_Formatter::second_to_minute_timestamp($statistics->average_session_duration()), 'growth' => $statistics->average_session_duration_percentage_growth(), 'unfiltered' => Number_Formatter::second_to_minute_timestamp($this->statistics->average_session_duration())];
        }
        if ($this->is_full_view() && \IAWP_SCOPED\iawp_using_woocommerce()) {
            $stats[] = ['title' => \__('Orders', 'iawp'), 'class' => 'orders', 'count' => \number_format($statistics->woocommerce_orders()), 'growth' => $statistics->woocommerce_orders_percentage_growth(), 'unfiltered' => \number_format($this->statistics->woocommerce_orders())];
            $stats[] = ['title' => \__('Net Sales', 'iawp'), 'class' => 'net-sales', 'count' => Currency::format($statistics->woocommerce_net_sales(), \true, \false), 'growth' => $statistics->woocommerce_net_sales_percentage_growth(), 'unfiltered' => Currency::format($this->statistics->woocommerce_net_sales())];
        }
        return \IAWP_SCOPED\iawp()->templates()->render('quick_stats', ['is_filtered' => $is_filtered, 'stats' => $stats]);
    }
}
