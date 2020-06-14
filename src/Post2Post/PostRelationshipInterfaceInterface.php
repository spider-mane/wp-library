<?php

namespace WebTheory\Post2Post;

use WP_Post;
use WP_Query;

interface PostRelationshipInterfaceInterface
{
    /**
     * @return string[]
     */
    public function getPostTypes(): array;

    /**
     * @return WP_Post[]
     */
    public function getRelatedPosts(WP_Post $post): array;

    /**
     * @return WP_Query
     */
    public function getRelatedPostsQuery(WP_Post $post): WP_Query;

    /**
     * @return WP_Post[]
     */
    public function getRelatedPostTypePosts(string $postType): array;

    /**
     * @return WP_Query
     */
    public function getRelatedPostTypePostsQuery(string $postType): WP_Query;

    /**
     * @return void
     */
    public function addPostRelationships(WP_Post $post, WP_Post ...$relatedPosts): void;

    /**
     * @return void
     */
    public function setPostRelationships(WP_Post $post, WP_Post ...$relatedPosts): void;

    /**
     * @return void
     */
    public function unsetPostRelationships(WP_Post $post, WP_Post ...$relatedPosts): void;
}
