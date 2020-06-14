<?php

namespace WebTheory\Post2Post\Exceptions;

use InvalidArgumentException;

class InvalidContextArgumentException extends InvalidArgumentException
{
    /**
     *
     */
    public function __construct(string $context)
    {
        $message = "$context is not a valid context for the provided relationship.";

        parent::__construct($message);
    }
}
