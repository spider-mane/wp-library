<?php

namespace Backalley\WP\Taxonomy\Args;

use Backalley\StructuralTaxonomy;


class StructuralTaxonomyArg implements CustomTaxonomyArgInterface
{
    public static function pass($taxonomy, $args)
    {
        return new StructuralTaxonomy($taxonomy, $args);
    }
}