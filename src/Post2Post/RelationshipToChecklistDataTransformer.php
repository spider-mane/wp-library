<?php

namespace WebTheory\Post2Post;

use WP_Post;
use WP_Query;
use WebTheory\Leonidas\Util\PostCollection;
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
        $posts = new PostCollection(...$posts);

        return array_map('strval', $posts->getIds());
    }

    /**
     *
     */
    public function reverseTransform($posts)
    {
        if (in_array('0', $posts)) {
            unset($posts[array_search('0', $posts)]);
        }

        if (!empty($posts)) {
            $query = new WP_Query([
                'post_type' => 'any',
                'post__in' => $posts,
                'posts_per_page' => -1,
                'suppress_filters' => true,
            ]);

            $posts = $query->get_posts();
        }

        return $posts;
    }
}
