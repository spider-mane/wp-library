<?php

namespace WebTheory\Post2Post;

use WP_Post_Type;
use WebTheory\Leonidas\PostType\OptionHandlerInterface;

class SomewhatRelatableToPostTypeArg implements OptionHandlerInterface
{
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
    public function check()
    {
        if (post_type_exists($this->relatedPostType)) {
            $this->create();
        } else {
            add_action('registered_post_type', function ($postType) {
                if ($postType === $this->relatedPostType) {
                    $this->create();
                }
            });
        }
    }

    /**
     *
     */
    public function create()
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

            $instance->check();
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
