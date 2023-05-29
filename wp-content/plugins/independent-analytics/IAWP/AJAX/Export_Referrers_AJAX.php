<?php

namespace IAWP_SCOPED\IAWP\AJAX;

use IAWP_SCOPED\IAWP\Capability_Manager;
use IAWP_SCOPED\IAWP\Date_Range\Exact_Date_Range;
use IAWP_SCOPED\IAWP\Queries\Referrers;
use IAWP_SCOPED\IAWP\Tables\Table_Referrers;
class Export_Referrers_AJAX extends AJAX
{
    protected function action_name() : string
    {
        return 'iawp_export_referrers';
    }
    protected function action_callback() : void
    {
        if (!Capability_Manager::can_edit()) {
            return;
        }
        $date_range = Exact_Date_Range::comprehensive_range();
        $referrers = new Referrers($date_range);
        $rows = $referrers->fetch();
        $table = new Table_Referrers();
        $csv = $table->csv($rows);
        echo \wp_kses($csv, 'post');
    }
}
