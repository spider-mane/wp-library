<?php

namespace Backalley\StructuredTaxonomy;

use Backalley\StructuredTaxonomy\StructuralTaxonomy;
use Backalley\Wordpress\Taxonomy\Deprecated\CustomTaxonomyArgInterface;


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
