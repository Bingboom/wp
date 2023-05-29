<?php

namespace IAWP_SCOPED\IAWP;

use IAWP_SCOPED\IAWP\Utils\WP_Async_Request;
class Geo_Database_Download_Job extends WP_Async_Request
{
    /**
     * @var string
     */
    protected $action = 'iawp_geo_database_download_job';
    /**
     * Handle
     *
     * Override this method to perform any actions required
     * during the async request.
     *
     * @return void
     */
    protected function handle() : void
    {
        $geo_database = new Geo_Database();
        $geo_database->download();
    }
}
