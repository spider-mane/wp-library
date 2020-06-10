<?php

namespace WebTheory\Post2Post;

class Repository
{
    /**
     * @var Model[]
     */
    protected static $relationships = [];

    /**
     *
     */
    public static function addRelationship(Model $relationship)
    {
        static::$relationships[$relationship->getName()] = $relationship;
    }

    /**
     *
     */
    public static function getRelationship(string $relationship)
    {
        return static::$relationships[$relationship];
    }
}
