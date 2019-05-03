<?php

namespace Backalley\WP\Taxonomy\Args;

use Backalley\SortableTaxonomy;


class SortableTaxonomyArg implements CustomTaxonomyArgInterface
{
    public static function pass($taxonomy, $args)
    {
        $post_type = $args['post_types'];
        unset($args['post_types']);

        return new SortableTaxonomy($taxonomy, $post_type, $args);
    }
}