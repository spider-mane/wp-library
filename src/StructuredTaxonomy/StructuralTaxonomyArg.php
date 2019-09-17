<?php

namespace Backalley\StructuredTaxonomy;

use Backalley\StructuredTaxonomy\StructuralTaxonomy;
use Backalley\Wordpress\Taxonomy\OptionHandlerInterface;

class StructuralTaxonomyArg implements OptionHandlerInterface
{
    /**
     * {@inheritDoc}
     */
    public static function handle(\WP_Taxonomy $taxonomy, $args)
    {
        new StructuralTaxonomy($taxonomy, $args);
    }
}
