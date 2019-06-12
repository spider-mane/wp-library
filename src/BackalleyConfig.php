<?php

namespace Backalley;

use Backalley\WordPress\PostType;
use Backalley\WordPress\Taxonomy;


/**
 * 
 */
class BackalleyConfig extends WpModuleLoader
{
    /**
     * @var int $stack the trace level of the call to get_directory() from the initial class method call
     */
    protected static $stack = 4;

    /**
     * @var regex
     */
    private static $delimeter;

    /**
     * 
     */
    protected static function get_args($file)
    {
        $file = file_get_contents(static::get_file($file, 'json'), false);

        return json_decode($file, true);
    }

    /**
     * 
     */
    public static function post_types(string $post_types = 'post-types')
    {
        return PostType::create(static::get_args($post_types));
    }

    /**
     * 
     */
    public static function taxonomies(string $taxonomies = 'taxonomies')
    {
        return Taxonomy::create(static::get_args($taxonomies));
    }
}