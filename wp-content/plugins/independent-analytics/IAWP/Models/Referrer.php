<?php

namespace IAWP_SCOPED\IAWP\Models;

use IAWP_SCOPED\IAWP\Utils\String_Util;
class Referrer
{
    use View_Stats;
    use WooCommerce_Stats;
    private $is_direct;
    private $referrer_ids;
    private $group_name;
    private $domain;
    private $type;
    public function __construct($row)
    {
        $this->is_direct = \is_null($row->referrer_ids);
        $this->referrer_ids = $row->referrer_ids;
        $this->group_name = $row->group_name;
        $this->domain = $row->domain;
        $this->type = $row->type;
        $this->set_view_stats($row);
        $this->set_wc_stats($row);
    }
    /**
     * Returns an array of all ids for a referrer group.
     * Direct traffic has an empty array.
     * Referrer traffic has an array with a single id.
     * Grouped referrer traffic has an array of multiple grouped referrer ids.
     *
     * @return array Associated ids
     */
    protected function ids() : array
    {
        if ($this->is_direct) {
            return [];
        }
        return $this->referrer_ids;
    }
    /**
     * Return group name, referrer url, or direct.
     *
     * @return string Referrer
     */
    // Todo - This is the one the table is doing...
    public function referrer() : string
    {
        if ($this->is_direct) {
            return 'Direct';
        }
        // For ungrouped referrers, strip any potential www. prefix
        if (String_Util::str_starts_with($this->group_name, 'www.')) {
            return \substr($this->group_name, 4);
        }
        return $this->group_name;
    }
    public function referrer_url() : string
    {
        if ($this->is_direct) {
            return 'Direct';
        }
        return $this->domain;
    }
    /**
     * Return group referrer type, referrer, or direct.
     *
     * @return string Referrer type
     */
    public function referrer_type() : string
    {
        if ($this->is_direct) {
            return 'Direct';
        }
        if (\is_null($this->type)) {
            return 'Referrer';
        }
        return $this->type;
    }
    /**
     * @return bool
     */
    public function is_direct() : bool
    {
        return $this->is_direct;
    }
    public static function referrer_ids(array $referrers) : array
    {
        if (\count($referrers) === 0) {
            return [];
        }
        $array_of_arrays = \array_map(function ($referrer) {
            if ($referrer->is_direct()) {
                return [null];
            }
            return $referrer->ids();
        }, $referrers);
        return \array_merge(...$array_of_arrays);
    }
}
