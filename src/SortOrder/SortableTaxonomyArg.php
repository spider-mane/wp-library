<?php

namespace Backalley\SortOrder;

use Backalley\SortOrder\SortableTaxonomy;
use Backalley\WordPress\Taxonomy\OptionHandlerInterface;
use Backalley\WordPress\Traits\RunsOnWpLoadedTrait;

class SortableTaxonomyArg implements OptionHandlerInterface
{
    use RunsOnWpLoadedTrait;

    /**
     *
     */
    protected $taxonomy;

    /**
     *
     */
    protected $postType;

    /**
     *
     */
    protected $args;

    /**
     *
     */
    protected function __construct($taxonomy, $postType, $args)
    {
        $this->taxonomy = $taxonomy;
        $this->postType = $postType;
        $this->args = $args;
    }

    /**
     *
     */
    public function run()
    {
        new SortableTaxonomy($this->taxonomy, $this->postType, $this->args);
    }

    /**
     *
     */
    public static function handle(\WP_Taxonomy $taxonomy, $args)
    {
        $postType = $args['post_types'];
        unset($args['post_types']);

        (new static($taxonomy->name, $postType, $args))->hook();
    }
}
