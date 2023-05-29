<?php

namespace IAWP_SCOPED\IAWP\AJAX;

use IAWP_SCOPED\IAWP\Capability_Manager;
use IAWP_SCOPED\IAWP\Date_Range\Exact_Date_Range;
use IAWP_SCOPED\IAWP\Queries\Resources;
use IAWP_SCOPED\IAWP\Tables\Table_Pages;
class Export_Views_AJAX extends AJAX
{
    protected function action_name() : string
    {
        return 'iawp_export_views';
    }
    protected function action_callback() : void
    {
        if (!Capability_Manager::can_edit()) {
            return;
        }
        $date_range = Exact_Date_Range::comprehensive_range();
        $resources = new Resources($date_range);
        $rows = $resources->fetch();
        $table = new Table_Pages();
        $csv = $table->csv($rows);
        echo \wp_kses($csv, 'post');
    }
}
