<?php

namespace IAWP_SCOPED\IAWP;

use IAWP_SCOPED\IAWP\Capability_Manager;
use IAWP_SCOPED\IAWP\Migrations;
class Reset_Database
{
    public function __construct()
    {
        $this->delete_all_iawp_options();
        Capability_Manager::reset_capabilities();
        Migrations\Migration::create_or_migrate();
    }
    private function delete_all_iawp_options()
    {
        global $wpdb;
        $prefix = 'iawp_';
        $options = $wpdb->get_results("SELECT * FROM {$wpdb->options} WHERE option_name LIKE '{$prefix}%'");
        foreach ($options as $option) {
            $name = $option->option_name;
            \delete_option($name);
        }
    }
}
