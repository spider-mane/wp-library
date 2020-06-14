<?php

namespace WebTheory\Post2Post;

use WP_Post;
use WP_Query;
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
        $relatedPosts = [];

        foreach ($posts as $post => $selected) {
            if ($selected) {
                $relatedPosts[] = $post;
            }
        }

        if (!empty($relatedPosts)) {
            $query = new WP_Query([
                'post_type' => 'any',
                'post__in' => $relatedPosts,
                'posts_per_page' => -1
            ]);

            $relatedPosts = $query->get_posts();
        }

        return $relatedPosts;
    }
}
