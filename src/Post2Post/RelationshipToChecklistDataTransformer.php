<?php

namespace WebTheory\Post2Post;

use WP_Post;
use WebTheory\Saveyour\Contracts\DataTransformerInterface;

class RelationshipToChecklistDataTransformer implements DataTransformerInterface
{
    /**
     * @param WP_Post[] $posts
     *
     * @return array
     */
    public function transform($posts)
    {
        return array_map(function (WP_Post $post) {
            return $post->post_name;
        }, $posts);
    }

    /**
     *
     */
    public function reverseTransform($posts)
    {
        $relationships = [];

        foreach ($posts as $post => $selected) {
            if ($selected) {
                $relationships['set'][] = (string) $post;
            } else {
                $relationships['unset'][] = (string) $post;
            }
        }

        return $relationships;
    }
}
