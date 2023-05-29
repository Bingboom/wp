<?php

namespace IAWP_SCOPED\IAWP\Queries;

use IAWP_SCOPED\IAWP\Date_Range\Date_Range;
use IAWP_SCOPED\IAWP\Models\Campaign;
use IAWP_SCOPED\IAWP\Query;
class Campaigns
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
    public function fetch()
    {
        if (\is_null($this->results)) {
            $this->results = $this->query();
        }
        return $this->results;
    }
    private function query()
    {
        $rows = Query::query('get_campaigns', ['start' => $this->date_range->iso_start(), 'end' => $this->date_range->iso_end(), 'prev_start' => $this->date_range->previous_period_iso_start(), 'prev_end' => $this->date_range->previous_period_iso_end()])->rows();
        return $this->rows_to_campaigns($rows);
    }
    private function rows_to_campaigns($rows)
    {
        return \array_map(function ($row) {
            $row->campaign_ids = \explode(',', $row->campaign_ids);
            return new Campaign($row);
        }, $rows);
    }
}
