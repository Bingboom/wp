<?php

namespace IAWP_SCOPED\IAWP;

use DateTime;
use IAWP_SCOPED\IAWP\Date_Range\Exact_Date_Range;
use IAWP_SCOPED\IAWP\Interval\Minute_Interval;
use IAWP_SCOPED\IAWP\Interval\Ten_Second_Interval;
use IAWP_SCOPED\IAWP\Queries\Campaigns;
use IAWP_SCOPED\IAWP\Queries\Country_Statistics;
use IAWP_SCOPED\IAWP\Queries\Current_Traffic_Finder;
use IAWP_SCOPED\IAWP\Queries\Referrers;
use IAWP_SCOPED\IAWP\Queries\Resources;
use IAWP_SCOPED\IAWP\Queries\Visitors_Over_Time_Finder;
use IAWP_SCOPED\IAWP\Utils\Singleton;
class Real_Time
{
    use Singleton;
    public function __construct()
    {
    }
    private function get_count_message(int $count, string $singular, string $plural) : string
    {
        return \number_format($count) . ' ' . \_n($singular, $plural, $count);
    }
    private function get_visitor_count_message(int $count) : string
    {
        return $this->get_count_message($count, \__('Active Visitor', 'iawp'), \__('Active Visitors', 'iawp'));
    }
    private function roundUpBySeconds(DateTime $datetime, $precision_seconds) : DateTime
    {
        $datetime = clone $datetime;
        $datetime->setTimestamp($precision_seconds * (int) \ceil($datetime->getTimestamp() / $precision_seconds));
        return $datetime;
    }
    public function get_real_time_analytics()
    {
        $thirty_minutes_ago = new DateTime('-30 minutes');
        $thirty_minutes_ago = $this->roundUpBySeconds($thirty_minutes_ago, 60);
        $five_minutes_ago = new DateTime('-5 minutes');
        $five_minutes_ago = $this->roundUpBySeconds($five_minutes_ago, 10);
        $now = new DateTime();
        $end_minutes = $this->roundUpBySeconds($now, 60);
        $end_seconds = $this->roundUpBySeconds($now, 10);
        $visitors_by_minute_date_range = new Exact_Date_Range($thirty_minutes_ago, $end_minutes, \false);
        $visitors_by_minute_finder = new Visitors_Over_Time_Finder($visitors_by_minute_date_range, new Minute_Interval());
        $visitors_by_minute = $visitors_by_minute_finder->fetch();
        $visitors_by_second_date_range = new Exact_Date_Range($five_minutes_ago, $end_seconds, \false);
        $visitors_by_second_finder = new Visitors_Over_Time_Finder($visitors_by_second_date_range, new Ten_Second_Interval());
        $visitors_by_second = $visitors_by_second_finder->fetch();
        $five_minute_date_range = new Exact_Date_Range($five_minutes_ago, new DateTime(), \false);
        $pages = new Resources($five_minute_date_range);
        $current_traffic_finder = new Current_Traffic_Finder($five_minute_date_range);
        $current_traffic = $current_traffic_finder->fetch();
        $page_data = \array_slice($pages->fetch(), 0, 10);
        $page_rows = \array_map(function ($row, $index) {
            return ['id' => $row->id(), 'position' => $index + 1, 'title' => $row->title(), 'views' => $row->views(), 'subtitle' => $row->most_popular_subtitle()];
        }, $page_data, \array_keys($page_data));
        $referrers = new Referrers($five_minute_date_range);
        $referrer_data = \array_slice($referrers->fetch(), 0, 10);
        $referrer_rows = \array_map(function ($row, $index) {
            return ['id' => $row->referrer(), 'position' => $index + 1, 'title' => $row->referrer(), 'views' => $row->views()];
        }, $referrer_data, \array_keys($referrer_data));
        $countries = new Country_Statistics($five_minute_date_range);
        $country_data = \array_slice($countries->fetch(), 0, 10);
        $country_rows = \array_map(function ($row, $index) {
            return ['id' => $row->country(), 'position' => $index + 1, 'title' => $row->country(), 'views' => $row->views(), 'flag' => $row->flag()];
        }, $country_data, \array_keys($country_data));
        $campaigns = new Campaigns($five_minute_date_range);
        $campaign_data = \array_slice($campaigns->fetch(), 0, 10);
        $campaign_rows = \array_map(function ($row, $index) {
            return ['id' => $row->params(), 'position' => $index + 1, 'title' => $row->utm_campaign(), 'views' => $row->views()];
        }, $campaign_data, \array_keys($campaign_data));
        $visitor_message = $this->get_visitor_count_message($current_traffic->get_visitor_count());
        $page_message = $this->get_count_message($current_traffic->get_page_count(), \__('Page', 'iawp'), \__('Pages', 'iawp'));
        $referrer_message = $this->get_count_message($current_traffic->get_referrer_count(), \__('Referrer', 'iawp'), \__('Referrers', 'iawp'));
        $country_message = $this->get_count_message($current_traffic->get_country_count(), \__('Country', 'iawp'), \__('Countries', 'iawp'));
        return ['visitor_message' => $visitor_message, 'page_message' => $page_message, 'referrer_message' => $referrer_message, 'country_message' => $country_message, 'chart_data' => ['minute_interval_visitors' => $visitors_by_minute->visitors, 'minute_interval_views' => $visitors_by_minute->views, 'minute_interval_labels_short' => $visitors_by_minute->interval_labels_short, 'minute_interval_labels_full' => $visitors_by_minute->interval_labels_full, 'second_interval_visitors' => $visitors_by_second->visitors, 'second_interval_views' => $visitors_by_second->views, 'second_interval_labels_short' => $visitors_by_second->interval_labels_short, 'second_interval_labels_full' => $visitors_by_second->interval_labels_full], 'lists' => ['pages' => ['title' => \__('Active Pages'), 'entries' => $page_rows], 'referrers' => ['title' => \__('Active Referrers'), 'entries' => $referrer_rows], 'countries' => ['title' => \__('Active Countries'), 'entries' => $country_rows], 'campaigns' => ['title' => \__('Active Campaigns'), 'entries' => $campaign_rows]]];
    }
    public function render_real_time_analytics()
    {
        echo \IAWP_SCOPED\iawp()->templates()->render('real_time', $this->get_real_time_analytics());
    }
}
