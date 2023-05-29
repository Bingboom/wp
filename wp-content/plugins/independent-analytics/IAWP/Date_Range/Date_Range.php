<?php

namespace IAWP_SCOPED\IAWP\Date_Range;

use IAWP_SCOPED\IAWP\Utils\Timezone;
use DateTime;
abstract class Date_Range
{
    /**
     * @var DateTime
     */
    protected $start;
    /**
     * @var DateTime
     */
    protected $end;
    public abstract function label() : string;
    protected function set_range(DateTime $start, DateTime $end, bool $convert_to_full_days)
    {
        $start = clone $start;
        $end = clone $end;
        if ($convert_to_full_days) {
            $start = $this->start_of_locale_day($start);
            $end = $this->end_of_locale_day($end);
        }
        $this->start = $start;
        $this->end = $end;
    }
    /**
     * @return DateTime
     */
    public function start() : DateTime
    {
        return $this->start;
    }
    /**
     * @return string
     */
    public function iso_start() : string
    {
        return $this->start->format('c');
    }
    /**
     * @return DateTime
     */
    public function end() : DateTime
    {
        return $this->end;
    }
    /**
     * @return string
     */
    public function iso_end() : string
    {
        return $this->end->format('c');
    }
    /**
     * @return DateTime
     */
    public function previous_period_start() : DateTime
    {
        $previous_start = clone $this->start;
        $range_size = $this->range_size_in_days();
        return $previous_start->modify("-{$range_size} days");
    }
    /**
     * @return string
     */
    public function previous_period_iso_start() : string
    {
        return $this->previous_period_start()->format('c');
    }
    /**
     * @return DateTime
     */
    public function previous_period_end() : DateTime
    {
        $previous_end = clone $this->end;
        $range_size = $this->range_size_in_days();
        return $previous_end->modify("-{$range_size} days");
    }
    /**
     * @return string
     */
    public function previous_period_iso_end() : string
    {
        return $this->previous_period_end()->format('c');
    }
    /**
     * Return the range size in days for previous period calculations
     *
     * @return int
     */
    private function range_size_in_days() : int
    {
        return $this->start->diff($this->end)->days + 1;
    }
    /**
     * Return a new DateTime representing the start of the day in the users timezone
     *
     * @param DateTime $datetime
     *
     * @return DateTime
     */
    private function start_of_locale_day(\DateTime $datetime) : \DateTime
    {
        $datetime = clone $datetime;
        return $datetime->setTimezone(Timezone::wordpress_site_timezone())->setTime(0, 0, 0)->setTimezone(Timezone::utc_timezone());
    }
    /**
     * Return a new DateTime representing the end of the day in the users timezone
     *
     * @param DateTime $datetime
     *
     * @return DateTime
     */
    private function end_of_locale_day(\DateTime $datetime) : \DateTime
    {
        $datetime = clone $datetime;
        return $datetime->setTimezone(Timezone::wordpress_site_timezone())->setTime(23, 59, 59)->setTimezone(Timezone::utc_timezone());
    }
}
