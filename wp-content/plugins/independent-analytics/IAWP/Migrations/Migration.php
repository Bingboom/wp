<?php

namespace IAWP_SCOPED\IAWP\Migrations;

abstract class Migration
{
    /**
     * Classes that extend must provide their database version
     *
     * @var string
     */
    protected $database_version = '0';
    public final function __construct()
    {
        $this->migrate();
    }
    /**
     * Trigger the migration
     *
     * @return void
     */
    protected final function migrate() : void
    {
        $db_version = \get_option('iawp_db_version', '0');
        if (\version_compare($db_version, $this->database_version, '<')) {
            // Trigger the classes' migration function
            $this->handle();
            \update_option('iawp_db_version', $this->database_version);
        }
    }
    /**
     * Classes that extend must define a handle method where migration work is done
     *
     * @return void
     */
    protected abstract function handle() : void;
    /**
     * @return void
     */
    public static function create_or_migrate() : void
    {
        if (self::should_migrate()) {
            \update_option('iawp_is_migrating', '1');
            new Migration_1_0();
            new Migration_1_6();
            new Migration_1_8();
            new Migration_1_9();
            new Migration_2();
            new Migration_3();
            new Migration_4();
            new Migration_5();
            new Migration_6();
            new Migration_7();
            new Migration_8();
            new Migration_9();
            new Migration_10();
            new Migration_11();
            new Migration_12();
            \update_option('iawp_is_migrating', '0');
        }
    }
    /**
     * is_migrating is serving multiple purposes. It's also being used to stop ajax requests and dashboard
     * widgets from running when the database version is newer than one that comes with the installed version
     * of independent analytics. The probably should be a method called something `database_ready` that serves
     * this purpose more explicitly.
     *
     * @return bool
     */
    public static function is_migrating() : bool
    {
        $db_version = \get_option('iawp_db_version', '0');
        $is_migrating = \get_option('iawp_is_migrating') === '1';
        $is_current = \version_compare($db_version, '12', '=');
        $is_outdated = !$is_current;
        return $is_outdated || $is_migrating;
    }
    public static function has_newer_database_version() : bool
    {
        $db_version = \get_option('iawp_db_version', '0');
        return \version_compare($db_version, '12', '>');
    }
    public static function is_actually_migrating() : bool
    {
        return \get_option('iawp_is_migrating') === '1';
    }
    /**
     * @return bool
     */
    public static function should_migrate() : bool
    {
        $db_version = \get_option('iawp_db_version', '0');
        $is_migrating = \get_option('iawp_is_migrating') === '1';
        $is_current = \version_compare($db_version, '12', '=');
        $is_outdated = !$is_current;
        return $is_outdated && !$is_migrating;
    }
}
