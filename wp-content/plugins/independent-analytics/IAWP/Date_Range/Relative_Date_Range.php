<?php

namespace IAWP_SCOPED\IAWP\Date_Range;

use DateTime;
use IAWP_SCOPED\IAWP\Utils\Timezone;
class Relative_Date_Range extends Date_Range
{
    /**
     * @var string
     */
    private $label;
    /**
     * @var string
     */
    private $relative_range_id = 'LAST_THIRTY';
    private const VALID_RELATIVE_RANGE_IDS = ['TODAY', 'YESTERDAY', 'LAST_SEVEN', 'LAST_THIRTY', 'THIS_WEEK', 'LAST_WEEK', 'THIS_MONTH', 'LAST_MONTH', 'THIS_YEAR', 'LAST_YEAR'];
    /**
     * @param string $relative_range_id A valid range id such as LAST_MONTH
     * @param bool $convert_to_full_days
     */
    public function __construct(string $relative_range_id, bool $convert_to_full_days = \true)
    {
        if (\in_array($relative_range_id, self::VALID_RELATIVE_RANGE_IDS)) {
            $this->relative_range_id = $relative_range_id;
        }
        // Call the method whose name matches the relative range id
        $method_name = \strtolower($relative_range_id);
        list($start, $end, $label) = $this->{$method_name}();
        $this->set_range($start, $end, $convert_to_full_days);
        $this->label = $label;
    }
    /**
     * Get the id of the current range such as THIS_YEAR
     *
     * @return string
     */
    public function relative_range_id() : string
    {
        return $this->relative_range_id;
    }
    public function label() : string
    {
        return $this->label;
    }
    /**
     * Returns an array of relative ranges representing all supported ranges
     *
     * @return Relative_Date_Range[]
     */
    public static function ranges() : array
    {
        return \array_map(function (string $range_id) {
            return new self($range_id);
        }, self::VALID_RELATIVE_RANGE_IDS);
    }
    /**
     * @param string $relative_range_id
     *
     * @return bool
     */
    public static function is_valid_range(string $relative_range_id) : bool
    {
        if (\in_array($relative_range_id, self::VALID_RELATIVE_RANGE_IDS)) {
            return \true;
        }
        return \false;
    }
    private function today() : array
    {
        $tz = Timezone::wordpress_site_timezone();
        $today = new DateTime('now', $tz);
        return [$today, $today, \__('Today', 'iawp')];
    }
    private function yesterday() : array
    {
        $tz = Timezone::wordpress_site_timezone();
        $yesterday = new DateTime('-1 day', $tz);
        return [$yesterday, $yesterday, \__('Yesterday', 'iawp')];
    }
    private function last_seven() : array
    {
        $tz = Timezone::wordpress_site_timezone();
        $today = new DateTime('now', $tz);
        $seven_days_ago = new DateTime('-6 days', $tz);
        return [$seven_days_ago, $today, \__('Last 7 Days', 'iawp')];
    }
    private function last_thirty() : array
    {
        $tz = Timezone::wordpress_site_timezone();
        $today = new DateTime('now', $tz);
        $thirty_days_ago = new DateTime('-29 days', $tz);
        return [$thirty_days_ago, $today, \__('Last 30 Days', 'iawp')];
    }
    private function this_week() : array
    {
        $tz = Timezone::wordpress_site_timezone();
        $today = new DateTime('now', $tz);
        $firstDayOfWeek = \intval(\get_option('iawp_dow'));
        $currentDayOfWeek = \intval($today->format('w'));
        $startOfWeekDaysAgo = $currentDayOfWeek - $firstDayOfWeek;
        if ($startOfWeekDaysAgo < 0) {
            $startOfWeekDaysAgo += 7;
        }
        $startOfWeek = new DateTime("-{$startOfWeekDaysAgo} days", $tz);
        $endOfWeek = (clone $startOfWeek)->modify('+6 days');
        return [$startOfWeek, $endOfWeek, \__('This Week', 'iawp')];
    }
    private function last_week() : array
    {
        list($start, $end) = $this->this_week();
        $startOfWeek = $start->modify('-7 days');
        $endOfWeek = $end->modify('-7 days');
        return [$startOfWeek, $endOfWeek, \__('Last Week', 'iawp')];
    }
    private function this_month() : array
    {
        $tz = Timezone::wordpress_site_timezone();
        $today = new DateTime('now', $tz);
        $day_of_month = \intval($today->format('d')) - 1;
        $days_in_month = \intval($today->format('t')) - 1;
        $start_of_month = (clone $today)->modify("-{$day_of_month} days");
        $end_of_month = (clone $start_of_month)->modify("+{$days_in_month} days");
        return [$start_of_month, $end_of_month, \__('This Month', 'iawp')];
    }
    private function last_month() : array
    {
        list($start, $end) = $this->this_month();
        $start_of_last_month = (clone $start)->modify('-1 month');
        $days_in_last_month = \intval($start_of_last_month->format('t')) - 1;
        $end_of_last_month = (clone $start_of_last_month)->modify("+{$days_in_last_month} days");
        return [$start_of_last_month, $end_of_last_month, \__('Last Month', 'iawp')];
    }
    private function this_year() : array
    {
        $tz = Timezone::wordpress_site_timezone();
        $today = new DateTime('now', $tz);
        $year = \intval($today->format('Y'));
        $start_of_year = (clone $today)->setDate($year, 1, 1);
        $end_of_year = (clone $today)->setDate($year, 12, 31);
        return [$start_of_year, $end_of_year, \__('This Year', 'iawp')];
    }
    private function last_year() : array
    {
        $tz = Timezone::wordpress_site_timezone();
        $today = new DateTime('now', $tz);
        $last_year = \intval($today->format('Y')) - 1;
        $start_of_last_year = (clone $today)->setDate($last_year, 1, 1);
        $end_of_last_year = (clone $today)->setDate($last_year, 12, 31);
        return [$start_of_last_year, $end_of_last_year, \__('Last Year', 'iawp')];
    }
}
