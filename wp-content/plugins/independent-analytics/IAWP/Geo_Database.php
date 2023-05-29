<?php

namespace IAWP_SCOPED\IAWP;

use IAWP_SCOPED\MaxMind\Db\Reader;
use IAWP_SCOPED\Proper\Gatekeeper;
class Geo_Database
{
    private $latest_geo_database_version = '2';
    private $latest_geo_database_checksum = '320bfae90bc6257f0cb52191cc410995';
    private $database_file_name = 'iawp-geo-db.mmdb';
    /**
     * TODO - Move this out
     *
     * @param $ip
     *
     * @return array|null
     */
    public function ip_to_geo($ip) : ?array
    {
        try {
            $reader = new Reader($this->database_path());
            $geo = $reader->get($ip);
            $reader->close();
            return $geo;
        } catch (\Exception $e) {
            return null;
        }
    }
    /**
     * Determine if an update to the geo database should occur
     *
     * @return bool
     */
    private function skip_update() : bool
    {
        $is_downloading = \get_option('iawp_is_database_downloading', '0') === '1';
        if ($is_downloading) {
            return \true;
        }
        $file_missing = \file_exists($this->database_path()) === \false;
        if ($file_missing) {
            return \false;
        }
        $gatekeeper = new Gatekeeper('iawp_geo_database_last_verified_at', 'PT30M');
        if ($gatekeeper->should_run()) {
            $gatekeeper->complete();
            $valid_checksum = \verify_file_md5($this->database_path(), $this->latest_geo_database_checksum);
            if (!$valid_checksum) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * @return void
     */
    public function maybe_dispatch_download_job()
    {
        if ($this->skip_update()) {
            return;
        }
        $download_job = new Geo_Database_Download_Job();
        $download_job->dispatch();
    }
    /**
     * @return void
     */
    public function download()
    {
        if ($this->skip_update()) {
            return;
        }
        \update_option('iawp_is_database_downloading', '1');
        try {
            \wp_remote_get($this->database_download_url(), ['stream' => \true, 'filename' => $this->zip_path(), 'timeout' => 60]);
            $zip = new \ZipArchive();
            if ($zip->open($this->zip_path()) === \true) {
                $zip->extractTo($this->wordpress_upload_folder_path());
                $zip->close();
            }
            \wp_delete_file($this->zip_path());
        } catch (\Exception $e) {
            // Do nothing
        }
        \update_option('iawp_is_database_downloading', '0');
    }
    /**
     * @return string Name of the zip file for the latest geo database version
     */
    private function zip_file_name() : string
    {
        return 'iawp-geo-db-' . $this->latest_geo_database_version . '.zip';
    }
    /**
     * @return string Path to WordPress upload directory
     */
    private function wordpress_upload_folder_path() : string
    {
        return \trailingslashit(\wp_upload_dir()['basedir']);
    }
    /**
     * @return string Path to downloaded database
     */
    private function zip_path() : string
    {
        return $this->wordpress_upload_folder_path() . $this->zip_file_name();
    }
    /**
     * @return string Path to downloaded database
     */
    private function database_path() : string
    {
        return $this->wordpress_upload_folder_path() . $this->database_file_name;
    }
    /**
     * @return string AWS Cloudfront URL
     */
    private function database_download_url() : string
    {
        return 'https://d12l9frnj72cgq.cloudfront.net/' . $this->zip_file_name();
    }
}
