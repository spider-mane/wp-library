<?php

namespace Backalley;

/**
 * 
 */
class WpModuleLoader
{
    /**
     * 
     */
    private static $stack = 3;

    /**
     * 
     */
    protected static function get_directory()
    {
        $dir = debug_backtrace(null, self::$stack);
        $dir = explode(DIRECTORY_SEPARATOR, $dir[self::$stack - 1]['file'], -1);
        $dir = implode(DIRECTORY_SEPARATOR, $dir) . DIRECTORY_SEPARATOR;

        return $dir;
    }

    /**
     * 
     */
    protected static function get_file($module)
    {
        $module = preg_replace('/\//', DIRECTORY_SEPARATOR, $module);
        return self::get_directory() . $module . '.php';
    }

    /**
     * 
     */
    public static function init(string $module)
    {
        $file = self::get_file($module);

        add_action('init', function () use ($file) {
            include $file;
        });
    }
}