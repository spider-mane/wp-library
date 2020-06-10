<?php

namespace WebTheory\Post2Post;

use WebTheory\Post2Post\Exceptions\InvalidContextArgumentException;

trait HasContextArgumentTrait
{
    /**
     *
     */
    protected function throwExceptionIfInvalidContext(string $context)
    {
        if ('related' !== $context && 'relatable' !== $context) {
            throw new InvalidContextArgumentException();
        }

        return $context;
    }
}
