<?php

namespace IAWP_SCOPED\IAWP\AJAX;

use IAWP_SCOPED\IAWP\Capability_Manager;
class Test_Email_AJAX extends AJAX
{
    protected function action_name() : string
    {
        return 'iawp_test_email';
    }
    protected function action_callback() : void
    {
        if (!Capability_Manager::can_edit()) {
            return;
        }
        $sent = \IAWP_SCOPED\iawp()->email_reports->send_email_report(\true);
        echo $sent;
    }
}
