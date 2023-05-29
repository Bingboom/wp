<?php

namespace IAWP_SCOPED\IAWP\Utils;

trait Singleton
{
    /**
     * Singleton Instance
     *
     * @var mixed
     */
    private static $instance = null;
    /**
     * Private Constructor
     *
     * We can't use the constructor to create an instance of the class
     *
     * @return void
     */
    private function __construct()
    {
        // Don't do anything, we don't want to be initialized
    }
    /**
     * Get the singleton instance
     *
     * @return self
     */
    public static function getInstance() : self
    {
        if (\is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * Private clone method to prevent cloning of the instance of the
     * Singleton instance.
     *
     * @return void
     */
    private function __clone()
    {
        // Don't do anything, we don't want to be cloned
    }
    /**
     * Private unserialize method to prevent unserializing of the Singleton
     * instance.
     *
     * @return void
     */
    public function __wakeup()
    {
        // Don't do anything, we don't want to be unserialized
    }
}
