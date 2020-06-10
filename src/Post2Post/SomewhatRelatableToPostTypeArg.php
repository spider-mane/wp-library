<?php

namespace WebTheory\Post2Post;

use WP_Post_Type;
use WebTheory\Leonidas\PostType\OptionHandlerInterface;
use WebTheory\Leonidas\Traits\RunsOnWpLoadedTrait;

class SomewhatRelatableToPostTypeArg implements OptionHandlerInterface
{
    // use RunsOnWpLoadedTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var WP_Post_Type
     */
    protected $relatablePostType;

    /**
     * @var string
     */
    protected $relatedPostType;

    /**
     *
     */
    protected function __construct($name, $relatablePostType, $relatedPostType)
    {
        $this->name = $name;
        $this->relatablePostType = $relatablePostType;
        $this->relatedPostType = $relatedPostType;
    }

    /**
     *
     */
    public function run()
    {
        $model = new Model(
            $this->name,
            $this->relatablePostType,
            get_post_type_object($this->relatedPostType)
        );

        $model->register();
    }

    /**
     *
     */
    public static function handle(WP_Post_Type $postType, $relationships)
    {
        foreach ($relationships as $relationship) {
            $instance = new static(
                $relationship['name'],
                $postType,
                $relationship['relatable_to']
            );

            $instance->run();
        }
    }

    /**
     *
     */
    public static function getArgName()
    {
        return 'post_relationships';
    }
}
