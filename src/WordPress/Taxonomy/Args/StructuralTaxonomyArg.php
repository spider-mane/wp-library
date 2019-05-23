<?php

namespace Backalley\WordPress\Taxonomy\Args;

use Backalley\StructuralTaxonomy;


class StructuralTaxonomyArg implements CustomTaxonomyArgInterface
{
    public static function pass($taxonomy, $args)
    {
        return new StructuralTaxonomy($taxonomy, $args);
    }

    public static function run()
    {
        return;
    }
}