<?php

namespace IAWP_SCOPED\IAWP\Queries;

use IAWP_SCOPED\IAWP\Date_Range\Date_Range;
use IAWP_SCOPED\IAWP\Models\Referrer;
use IAWP_SCOPED\IAWP\Query;
class Referrers
{
    /**
     * @var Date_Range
     */
    private $date_range;
    private $results;
    /**
     * @param Date_Range $date_range Range to fetch referrers for
     */
    public function __construct(Date_Range $date_range)
    {
        $this->date_range = $date_range;
    }
    public function fetch() : array
    {
        if (\is_null($this->results)) {
            $this->results = $this->query();
        }
        return $this->results;
    }
    private function query() : array
    {
        $rows = Query::query('get_referrers', ['start' => $this->date_range->iso_start(), 'end' => $this->date_range->iso_end(), 'prev_start' => $this->date_range->previous_period_iso_start(), 'prev_end' => $this->date_range->previous_period_iso_end()])->rows();
        return self::rows_to_referrers($rows);
    }
    private function rows_to_referrers($rows) : array
    {
        return \array_map(function ($row) {
            $has_referrers = !\is_null($row->referrer_ids);
            if ($has_referrers) {
                $row->referrer_ids = \explode(',', $row->referrer_ids);
            }
            return new Referrer($row);
        }, $rows);
    }
}
