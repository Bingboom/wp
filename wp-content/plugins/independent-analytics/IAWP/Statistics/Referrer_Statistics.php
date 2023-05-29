<?php

namespace IAWP_SCOPED\IAWP\Statistics;

class Referrer_Statistics extends Statistics
{
    protected function allowed_in_id_column() : string
    {
        return 'sessions.referrer_id';
    }
    /**
     * Referrers are allowed to have a null value in the id column. This would be a null value for
     * sessions.referrer_id, which is perfectly fine as these are direct visits. Return true if
     * direct visits are to be included, otherwise return false. This is a workaround of the fact
     * the direct visits do not have an id for the MySQL IN clause.
     *
     * @return bool
     */
    protected function allow_null_in_id_column() : bool
    {
        return \in_array(null, $this->allowed_ids);
    }
}
