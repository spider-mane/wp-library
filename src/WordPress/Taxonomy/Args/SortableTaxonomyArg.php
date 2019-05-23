<?php

namespace Backalley\WordPress\Taxonomy\Args;

use Backalley\SortableTaxonomy;


class SortableTaxonomyArg implements CustomTaxonomyArgInterface
{
    public static $taxonomy;
    public static $post_type;
    public static $args;

    public static function pass($taxonomy, $args)
    {
        Self::$taxonomy = $taxonomy;
        Self::$post_type = $args['post_types'];

        unset($args['post_types']);
        Self::$args = $args;
    }

    public static function run()
    {
        return new SortableTaxonomy(Self::$taxonomy, Self::$post_type, Self::$args);
    }
}