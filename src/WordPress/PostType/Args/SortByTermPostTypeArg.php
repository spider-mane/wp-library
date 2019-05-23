<?php

namespace Backalley\WordPress\PostType\Args;

use Backalley\SortablePostsInTerm;

class SortByTermPostTypeArg implements CustomArgInterface
{
    public static $post_type;
    public static $taxonomy;
    public static $args;

    public static function pass($post_type, $args)
    {
        Self::$post_type = $post_type->name;
        Self::$taxonomy = $args['taxonomy'];

        unset($args['taxonomy']);
        Self::$args = $args;
    }

    public static function run()
    {
        return new SortablePostsInTerm(Self::$post_type, Self::$taxonomy, Self::$args);
    }
}