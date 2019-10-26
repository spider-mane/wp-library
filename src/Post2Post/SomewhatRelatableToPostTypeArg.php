<?php

namespace WebTheory\Post2Post;

use WebTheory\Post2Post\SomewhatRelatablePostType;
use WebTheory\Leonidas\PostType\OptionHandlerInterface;
use WebTheory\Leonidas\Traits\RunsOnWpLoadedTrait;

class SomewhatRelatableToPostTypeArg implements OptionHandlerInterface
{
    use RunsOnWpLoadedTrait;

    /**
     *
     */
    protected $relatablePostType;

    /**
     *
     */
    protected $relatedPostType;

    /**
     *
     */
    protected function __construct($relatablePostType, $relatedPostType)
    {
        $this->relatablePostType = $relatablePostType;
        $this->relatedPostType = $relatedPostType;
    }

    /**
     *
     */
    public function run()
    {
        new SomewhatRelatablePostType($this->relatablePostType, $this->relatedPostType);
    }

    /**
     *
     */
    public static function handle(\WP_Post_Type $postType, $relatedPostType)
    {
        (new static($postType->name, $relatedPostType))->hook();
    }
}
