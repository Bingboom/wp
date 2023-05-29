<?php

namespace IAWP_SCOPED;

use  IAWP_SCOPED\IAWP\Geo_Database ;
use  IAWP_SCOPED\IAWP\Independent_Analytics ;
use  IAWP_SCOPED\IAWP\Migrations ;
use  IAWP_SCOPED\IAWP\WP_Option_Cache_Bust ;
\define( 'IAWP_DIRECTORY', \rtrim( \plugin_dir_path( __FILE__ ), \DIRECTORY_SEPARATOR ) );
\define( 'IAWP_URL', \rtrim( \plugin_dir_url( __FILE__ ), '/' ) );
\define( 'IAWP_VERSION', '1.23.1' );
\define( 'IAWP_LANGUAGES_DIRECTORY', \dirname( \plugin_basename( __FILE__ ) ) . '/languages' );

if ( \file_exists( \IAWP_SCOPED\iawp_path_to( 'vendor/scoper-autoload.php' ) ) ) {
    require_once \IAWP_SCOPED\iawp_path_to( 'vendor/scoper-autoload.php' );
} else {
    require_once \IAWP_SCOPED\iawp_path_to( 'vendor/autoload.php' );
}

/**
 * @param $log
 *
 * @return void
 */
function iawp_log( $log ) : void
{
    if ( \WP_DEBUG === \true && \WP_DEBUG_LOG === \true ) {
        
        if ( \is_array( $log ) || \is_object( $log ) ) {
            \error_log( \print_r( $log, \true ) );
        } else {
            \error_log( $log );
        }
    
    }
}

/**
 * @param $path
 *
 * @return string
 */
function iawp_path_to( $path ) : string
{
    $path = \trim( $path, \DIRECTORY_SEPARATOR );
    return \implode( \DIRECTORY_SEPARATOR, [ \IAWP_DIRECTORY, $path ] );
}

/**
 * @param $path
 *
 * @return string
 */
function iawp_url_to( $path ) : string
{
    $path = \trim( $path, '/' );
    return \implode( '/', [ \IAWP_URL, $path ] );
}

/**
 * Provide defaults for arguments provided as an associated array
 *
 * @param array $args
 * @param array $defaults
 *
 * @return array
 */
function iawp_default_args( array $args = array(), array $defaults = array() ) : array
{
    $args = \array_filter( $args, function ( $value ) {
        // Remove key/value pairs where value is null
        return $value !== null;
    } );
    $args = \array_intersect_key( $args, $defaults );
    return \array_replace( $defaults, $args );
}

/**
 * Determines if the user is running a licensed pro version
 *
 * @return bool
 */
function iawp_is_pro() : bool
{
    return \false;
}

/**
 * Determines if the user is running a free version or an unlicensed pro version
 * @return bool
 */
function iawp_is_free() : bool
{
    return !\IAWP_SCOPED\iawp_is_pro();
}

/**
 * Determines if a pro user has WooCommerce activated
 * @return bool
 */
function iawp_using_woocommerce() : bool
{
    global  $wpdb ;
    if ( \IAWP_SCOPED\iawp_is_free() ) {
        return \false;
    }
    $class_missing = \class_exists( '\\WooCommerce' ) === \false;
    if ( $class_missing ) {
        return \false;
    }
    $table_name = $wpdb->prefix . 'wc_order_stats';
    $order_stats_table = $wpdb->get_row( $wpdb->prepare( '
                SELECT * FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s
            ', $wpdb->dbname, $table_name ) );
    if ( \is_null( $order_stats_table ) ) {
        return \false;
    }
    return \true;
}

function iawp()
{
    return Independent_Analytics::getInstance();
}

\IAWP_SCOPED\iawp();
WP_Option_Cache_Bust::register( 'iawp_is_migrating' );
WP_Option_Cache_Bust::register( 'iawp_is_database_downloading' );
WP_Option_Cache_Bust::register( 'iawp_db_version' );
WP_Option_Cache_Bust::register( 'iawp_geo_database_version' );
\register_activation_hook( __DIR__ . '/iawp.php', function () {
    
    if ( \get_option( 'iawp_db_version', '0' ) === '0' ) {
        // If there is no database installed, run migration on current process
        Migrations\Migration::create_or_migrate();
    } else {
        // If there is a database, run migration in a background process
        Migrations\Migration_Job::maybe_dispatch();
    }
    
    $geo_database = new Geo_Database();
    $geo_database->maybe_dispatch_download_job();
    \update_option( 'iawp_need_clear_cache', \true );
    if ( \IAWP_SCOPED\iawp_is_pro() ) {
        \IAWP_SCOPED\iawp()->email_reports->schedule_email_report();
    }
} );
\register_deactivation_hook( __DIR__ . '/iawp.php', function () {
    if ( \IAWP_SCOPED\iawp_is_pro() ) {
        \IAWP_SCOPED\iawp()->email_reports->unschedule_email_report();
    }
} );
/*
* The admin_init hook will fire when the dashboard is loaded or an admin ajax request is made
*/
\add_action( 'admin_init', function () {
    new Migrations\Migration_Job();
    
    if ( \get_option( 'iawp_db_version', '0' ) === '0' ) {
        // If there is no database installed, run migration on current process
        Migrations\Migration::create_or_migrate();
    } else {
        // If there is a database, run migration in a background process
        Migrations\Migration_Job::maybe_dispatch();
    }
    
    $geo_database = new Geo_Database();
    $geo_database->maybe_dispatch_download_job();
} );