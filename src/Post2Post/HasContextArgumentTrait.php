<?php

namespace WebTheory\Post2Post;

use WebTheory\Post2Post\Exceptions\InvalidContextArgumentException;

trait HasContextArgumentTrait
{
    /**
     *
     */
    protected function throwExceptionIfInvalidContext(string $context, PostRelationshipInterfaceInterface $relationship)
    {
        if (!in_array($context, $relationship->getPostTypes())) {
            throw new InvalidContextArgumentException($context);
        }

        return $context;
    }
}
