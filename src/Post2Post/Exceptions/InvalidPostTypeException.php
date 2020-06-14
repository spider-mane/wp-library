<?php

namespace WebTheory\Post2Post\Exceptions;

use InvalidArgumentException;

class InvalidPostTypeException extends InvalidArgumentException
{
    /**
     * {@inheritDoc}
     */
    public function __construct(string $postType)
    {
        $message = "The provided post type: \"$postType\" is not part of this relationship.";

        parent::__construct($message);
    }
}
