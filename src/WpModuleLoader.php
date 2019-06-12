<?php

namespace Backalley;

/**
 * 
 */
class WpModuleLoader
{
    /**
     * @var int $stack the trace level of the call to get_directory() from the initial class method call
     */
    protected static $stack = 4;

    /**
     * 
     */
    protected static function register_hook(string $load, string $module, int $priority = null, $extension = 'php')
    {
        $file = static::get_file($module, $extension);

        add_action($load, function () use ($file) {
            include $file;

        }, $priority, 0);
    }

    /**
     * 
     */
    protected static function get_file($module, $ext)
    {
        return static::get_directory() . preg_replace('/\//', DIRECTORY_SEPARATOR, $module) . '.' . $ext;
    }

    /**
     * 
     */
    protected static function get_directory()
    {
        $dir = debug_backtrace(null, static::$stack);
        $dir = explode(DIRECTORY_SEPARATOR, $dir[static::$stack - 1]['file'], -1);
        $dir = implode(DIRECTORY_SEPARATOR, $dir) . DIRECTORY_SEPARATOR;

        return $dir;
    }

    /**
     * 
     */
    public static function hook(string $load, string $module, int $priority = null, $extension = 'php')
    {
        static::register_hook($hook, $module, $priority, $extension);
    }

    /**
     * 
     */
    public static function init(string $module, int $priority = null, string $extension = 'php')
    {
        static::register_hook('init', $module, $priority, $extension);
    }

    /**
     * 
     */
    public static function admin_enqueue(string $module, int $priority = null, string $extension = 'php')
    {
        static::register_hook('admin_enqueue_scripts', $module, $priority, $extension);
    }
}