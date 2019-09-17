<?php

namespace Backalley\SortOrder;

use Backalley\SortOrder\SortablePostsInTerm;
use Backalley\WordPress\PostType\OptionHandlerInterface;
use Backalley\Wordpress\Traits\RunsOnWpLoadedTrait;

class SortByTermPostTypeArg implements OptionHandlerInterface
{
    use RunsOnWpLoadedTrait;

    /**
     *
     */
    public $postType;

    /**
     *
     */
    public $taxonomy;

    /**
     *
     */
    public $args;

    /**
     *
     */
    protected function __construct($postType, $taxonomy, $args)
    {
        $this->postType = $postType;
        $this->taxonomy = $taxonomy;
        $this->args = $args;
    }

    /**
     *
     */
    public function run()
    {
        return new SortablePostsInTerm($this->postType, $this->taxonomy, $this->args);
    }

    /**
     *
     */
    public static function handle(\WP_Post_Type $postType, $args)
    {
        $taxonomy = $args['taxonomy'];
        unset($args['taxonomy']);

        (new static($postType->name, $taxonomy, $args))->hook();
    }
}
