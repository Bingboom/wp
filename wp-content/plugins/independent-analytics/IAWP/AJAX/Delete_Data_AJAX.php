<?php

namespace IAWP_SCOPED\IAWP\AJAX;

use IAWP_SCOPED\IAWP\Capability_Manager;
use IAWP_SCOPED\IAWP\Reset_Database;
class Delete_Data_AJAX extends AJAX
{
    protected function action_name() : string
    {
        return 'iawp_delete_data';
    }
    protected function action_callback() : void
    {
        if (!Capability_Manager::can_edit()) {
            return;
        }
        $confirmation = $this->get_field('confirmation');
        $valid = \strtolower($confirmation) == 'delete all data';
        if (!$valid) {
            return;
        }
        new Reset_Database();
    }
}
