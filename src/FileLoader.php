<?php

namespace Backalley;

/**
 * 
 */
abstract class FileLoader
{
    /**
     * @var int $stack  The trace level of the call to get_directory() 
     *                  from the initial class method call
     */
    protected static $stack;

    /**
     * @var string
     */
    protected static $delimeter = '/';

    /**
     * 
     */
    protected static function get_file($module, $ext)
    {
        return static::get_directory() . str_replace(static::$delimeter, DIRECTORY_SEPARATOR, $module) . '.' . $ext;
    }

    /**
     * 
     */
    protected static function get_directory()
    {
        $dir = debug_backtrace(null, static::$stack);
        $dir = dirname($dir[static::$stack - 1]['file']);

        return $dir . DIRECTORY_SEPARATOR;
    }
}