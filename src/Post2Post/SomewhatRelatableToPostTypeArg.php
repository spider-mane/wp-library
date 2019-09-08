<?php

/**
 *
 */

namespace Backalley\Post2Post;

use Backalley\Post2Post\SomewhatRelatablePostType;
use Backalley\Wordpress\PostType\Deprecated\CustomArgInterface;


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
