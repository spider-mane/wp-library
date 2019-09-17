<?php

namespace Backalley\Post2Post;

use Backalley\Post2Post\SomewhatRelatablePostType;
use Backalley\WordPress\PostType\OptionHandlerInterface;
use Backalley\Wordpress\Traits\RunsOnWpLoadedTrait;

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
