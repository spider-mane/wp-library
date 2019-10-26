<?php

namespace WebTheory\TaxRoles;

use WebTheory\TaxRoles\StructuralTaxonomy;
use WebTheory\Leonidas\Taxonomy\OptionHandlerInterface;

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
