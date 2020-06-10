<?php

namespace WebTheory\TaxRoles;

use WebTheory\Leonidas\Taxonomy\OptionHandlerInterface;
use WebTheory\TaxRoles\StructuralTaxonomy;

class StructuralTaxonomyArg implements OptionHandlerInterface
{
    /**
     *
     */
    protected $args;

    /**
     *
     */
    protected function __construct(array $args)
    {
        $this->args = $args;
    }

    /**
     *
     */
    public function run()
    {
        new StructuralTaxonomy(
            $this->args['taxonomy'],
            $this->args['roles'],
            $this->args['top'],
            $this->args['bottom']
        );
    }

    /**
     *
     */
    public function hook()
    {
        add_action('admin_init', [$this, 'run']);
    }

    /**
     * {@inheritDoc}
     */
    public static function handle(\WP_Taxonomy $taxonomy, $args)
    {
        (new static(['taxonomy' => $taxonomy->name] + $args))->hook();
    }
}
