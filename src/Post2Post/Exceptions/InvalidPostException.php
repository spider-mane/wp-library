<?php

namespace WebTheory\Post2Post\Exceptions;

use InvalidArgumentException;
use WP_Post;

class InvalidPostException extends InvalidArgumentException
{
    /**
     *
     */
    public function __construct(WP_Post $post)
    {
        $name = $post->post_name;
        $id = $post->ID;
        $postType = $post->post_type;

        $message = "The post \"$name\" with ID of $id has a post type of $postType which is not part in this relationship.";

        parent::__construct($message);
    }
}
