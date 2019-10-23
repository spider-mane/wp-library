<?php

namespace Backalley\TaxRoles;

use Backalley\TaxRoles\StructuralTaxonomy;
use Backalley\WordPress\Taxonomy\OptionHandlerInterface;

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
