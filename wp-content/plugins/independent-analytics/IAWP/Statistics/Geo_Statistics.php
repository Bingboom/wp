<?php

namespace IAWP_SCOPED\IAWP\Statistics;

class Geo_Statistics extends Statistics
{
    protected function allowed_in_id_column() : string
    {
        return 'sessions.visitor_id';
    }
    protected function required_column() : ?string
    {
        return 'visitors.country_code';
    }
}
