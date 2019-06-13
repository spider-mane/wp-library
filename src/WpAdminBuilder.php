<?php

namespace Backalley;

use Backalley\WordPress\PostType;
use Backalley\WordPress\Taxonomy;


/**
 * 
 */
class WpAdminBuilder extends FileLoader
{
    /**
     * 
     */
    protected static $stack = 4;

    /**
     * Undocumented variable
     *
     * @var string
     */
    public static $config_dir = 'config';

    /**
     * 
     */
    protected static function get_args($file)
    {
        $file = static::get_file(static::$config_dir . DIRECTORY_SEPARATOR . $file, 'json');
        $content = file_get_contents($file, false);

        return json_decode($content, true);
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