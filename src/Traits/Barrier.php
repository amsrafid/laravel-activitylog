<?php

namespace Amsrafid\ActivityLog\Traits;

/**
 * Activity log barrier
 */
trait Barrier
{
    /**
     * Log barrier variable
     * 
     * @var bool
     */
    private static $isPaused = false;

    /**
     * Pause Logging operation
     * 
     * @return void
     */
    public static function paused()
    {
        self::$isPaused = true;
    }

    /**
     * Proceed Logging operation
     * 
     * @return void
     */
    public static function proceed()
    {
        self::$isPaused = false;
    }

    /**
     * Check logging is paused or not
     * 
     * @return bool
     */
    public static function isPaused()
    {
        return self::$isPaused;
    }
}
