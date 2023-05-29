<?php

namespace IAWP_SCOPED\IAWP\AJAX;

use IAWP_SCOPED\IAWP\Real_Time;
class Real_Time_AJAX extends AJAX
{
    protected function action_name() : string
    {
        return 'iawp_real_time';
    }
    protected function action_callback() : void
    {
        $real_time = new Real_Time();
        \wp_send_json_success($real_time->get_real_time_analytics());
    }
}
