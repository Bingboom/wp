<?php

namespace IAWP_SCOPED\IAWP\AJAX;

use IAWP_SCOPED\IAWP\Capability_Manager;
class Cleared_Cache_AJAX extends AJAX
{
    protected function action_name() : string
    {
        return 'iawp_need_clear_cache';
    }
    protected function action_callback() : void
    {
        if (!Capability_Manager::can_edit()) {
            return;
        }
        \update_option('iawp_need_clear_cache', \false);
    }
}
