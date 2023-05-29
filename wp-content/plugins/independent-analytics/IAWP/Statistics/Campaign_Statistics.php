<?php

namespace IAWP_SCOPED\IAWP\Statistics;

class Campaign_Statistics extends Statistics
{
    protected function allowed_in_id_column() : string
    {
        return 'sessions.campaign_id';
    }
    protected function required_column() : ?string
    {
        return 'sessions.campaign_id';
    }
}
