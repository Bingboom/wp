<?php

namespace IAWP_SCOPED\IAWP\AJAX;

use IAWP_SCOPED\IAWP\Migrations;
class Migration_Status_AJAX extends AJAX
{
    protected function action_name() : string
    {
        return 'iawp_migration_status';
    }
    protected function allowed_during_migrations() : bool
    {
        return \true;
    }
    protected function action_callback() : void
    {
        if (!Migrations\Migration::is_actually_migrating()) {
            Migrations\Migration::create_or_migrate();
        }
        \wp_send_json_success(['isMigrating' => Migrations\Migration::is_migrating()]);
    }
}
