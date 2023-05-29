<?php

namespace IAWP_SCOPED\IAWP\Statistics;

class Page_Statistics extends Statistics
{
    protected function allowed_in_id_column() : string
    {
        return 'views.resource_id';
    }
}
