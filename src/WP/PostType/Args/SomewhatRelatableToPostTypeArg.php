<?php

/**
 * 
 */

namespace Backalley\WP\PostType\Args;

use Backalley\SomewhatRelatablePostType;

class SomewhatRelatableToPostTypeArg implements CustomArgInterface
{
    public static $relatable_post_type;
    public static $related_post_type;

    public static function pass($post_type, $related_post_type)
    {
        Self::$relatable_post_type = $post_type->name;
        Self::$related_post_type = $related_post_type;
    }

    public static function run()
    {
        return new SomewhatRelatablePostType(Self::$relatable_post_type, Self::$related_post_type);
    }
}