<?php

namespace IAWP_SCOPED\IAWP\Migrations;

use IAWP_SCOPED\IAWP\Known_Referrers;
class Migration_6 extends Migration
{
    /**
     * @var string
     */
    protected $database_version = '6';
    /**
     * @return void
     */
    protected function handle() : void
    {
        Known_Referrers::update_known_referrers_database();
    }
}
