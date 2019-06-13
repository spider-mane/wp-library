<?php

namespace Backalley;

/**
 * 
 */
class WpModuleLoader extends FileLoader
{
    /**
     * 
     */
    protected static $stack = 4;

    /**
     * 
     */
    protected static function register_hook(string $hook, string $module, int $priority = null, string $extension)
    {
        $file = static::get_file($module, $extension);

        add_action($hook, function () use ($file) {
            include $file;

        }, $priority, 0);
    }

    /**
     * 
     */
    public static function hook(string $hook, string $module, int $priority = null, string $extension = 'php')
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
