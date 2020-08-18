<?php

namespace WebTheory\Post2Post;

use WP_Post;
use WP_Post_Type;
use WP_Query;
use WP_Taxonomy;
use WebTheory\Leonidas\Util\PostCollection;
use WebTheory\Post2Post\Exceptions\InvalidPostException;
use WebTheory\Post2Post\Exceptions\InvalidPostTypeException;

class PostRelationship implements PostRelationshipInterfaceInterface
{
    /**
     * @var WP_Post_Type
     */
    protected $postType1;

    /**
     * @var WP_Post_Type
     */
    protected $postType2;

    /**
     * @var WP_Taxonomy
     */
    protected $postType1Shadow;

    /**
     * @var WP_Taxonomy
     */
    protected $postType2Shadow;

    /**
     *
     */
    public function __construct(string $postType1, string $postType2)
    {
        $this->postType1 = get_post_type_object($postType1);
        $this->postType2 = get_post_type_object($postType2);
        $this->postType1Shadow = SomewhatRelatablePostType::getShadow($postType1);
        $this->postType2Shadow = SomewhatRelatablePostType::getShadow($postType2);
    }

    /**
     *
     */
    public function getPostTypes(): array
    {
        return [$this->postType1->name, $this->postType2->name];
    }

    /**
     *
     */
    protected function getRelatedPostType(WP_Post $post): WP_Post_Type
    {
        switch ($post->post_type) {

            case $this->postType1->name:
                return $this->postType2;

            case $this->postType2->name:
                return $this->postType1;

            default:
                throw new InvalidPostException($post);
        }
    }

    /**
     *
     */
    protected function getRelatedPostTypeShadow(WP_Post $post): WP_Taxonomy
    {
        switch ($post->post_type) {

            case $this->postType1->name:
                return $this->postType2Shadow;

            case $this->postType2->name:
                return $this->postType1Shadow;

            default:
                throw new InvalidPostException($post);
        }
    }

    /**
     *
     */
    public function getRelatedPostTypeName(string $postType): string
    {
        $postType1 = $this->postType1->name;
        $postType2 = $this->postType2->name;

        switch ($postType) {

            case $postType1:
                return $postType2;

            case $postType2:
                return $postType1;

            default:
                throw new InvalidPostTypeException($postType);
        }
    }

    /**
     *
     */
    protected function getPostTypeShadow(WP_Post $post): WP_Taxonomy
    {
        switch ($post->post_type) {

            case $this->postType1->name:
                return $this->postType1Shadow;

            case $this->postType2->name:
                return $this->postType2Shadow;

            default:
                throw new InvalidPostException($post);
        }
    }

    /**
     *
     */
    public function getRelatedPostsQuery(WP_Post $post, int $count = -1): WP_Query
    {
        return new WP_Query([
            'post_type' => $this->getRelatedPostType($post)->name,
            'posts_per_page' => $count,
            'orderby' => 'name',
            'order' => 'ASC',
            'tax_query' => [[
                'taxonomy' => $this->getPostTypeShadow($post)->name,
                'terms' => $this->slugifyPost($post),
                'field' => 'slug',
                'include_children' => false,
            ]]
        ]);
    }

    /**
     *
     */
    public function getRelatedPostTypePostsQuery(string $postType, int $count = -1): WP_Query
    {
        return new WP_Query([
            'post_type' => $this->getRelatedPostTypeName($postType),
            'posts_per_page' => $count,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);
    }

    /**
     *
     */
    protected function slugifyPost(WP_Post $post): string
    {
        return (string) $post->ID;
    }

    /**
     *
     */
    protected function slugifyPosts(WP_Post ...$posts): array
    {
        return array_map([$this, 'slugifyPost'], $posts);
    }

    /**
     *
     */
    protected function defineRelationships(WP_Post $post, WP_Post ...$relatedPosts): void
    {
        wp_set_object_terms(
            $post->ID,
            $this->slugifyPosts(...$relatedPosts),
            $this->getRelatedPostTypeShadow($post)->name,
            false
        );
    }

    /**
     *
     */
    protected function extendRelationships(WP_Post $post, WP_Post ...$relatedPosts): void
    {
        wp_set_object_terms(
            $post->ID,
            $this->slugifyPosts(...$relatedPosts),
            $this->getRelatedPostTypeShadow($post)->name,
            true // ! must be set to true in order to avoid completely rewriting relationships!
        );
    }

    /**
     *
     */
    protected function removeRelationships(WP_Post $post, WP_Post ...$relatedPosts): void
    {
        wp_remove_object_terms(
            $post->ID,
            $this->slugifyPosts(...$relatedPosts),
            $this->getRelatedPostTypeShadow($post)->name
        );
    }

    /**
     * @return WP_Post[]
     */
    public function getRelatedPosts(WP_Post $post, int $count = -1): array
    {
        return $this->getRelatedPostsQuery($post, $count)->get_posts();
    }

    /**
     * @return int[]
     */
    public function getRelatedPostsIds(WP_Post $post, int $count = -1): array
    {
        $query = $this->getRelatedPostsQuery($post, $count);
        $query->set('fields', 'ids');

        return $query->get_posts();
    }

    /**
     * @return WP_Post[]
     */
    public function getRelatedPostTypePosts(string $postType, int $count = -1): array
    {
        return $this->getRelatedPostTypePostsQuery($postType, $count)->get_posts();
    }

    /**
     *
     *
     * @param WP_Post $post The post to set the relationships of
     */
    public function setPostRelationships(WP_Post $post, WP_Post ...$relatedPosts): void
    {
        $original = new PostCollection(...$this->getRelatedPosts($post));
        $estrangedPosts = $original->without(new PostCollection(...$relatedPosts));

        $this->defineRelationships($post, ...$relatedPosts);

        foreach ($relatedPosts as $relatedPost) {
            $this->extendRelationships($relatedPost, $post);
        }

        foreach ($estrangedPosts as $estrangedPost) {
            $this->removeRelationships($estrangedPost, $post);
        }
    }

    /**
     *
     */
    public function addPostRelationships(WP_Post $post, WP_Post ...$relatedPosts): void
    {
        $this->extendRelationships($post, ...$relatedPosts);

        foreach ($relatedPosts as $relatedPost) {
            $this->extendRelationships($relatedPost, $post);
        }
    }

    /**
     *
     */
    public function unsetPostRelationships(WP_Post $post, WP_Post ...$relatedPosts): void
    {
        $this->removeRelationships($post, ...$relatedPosts);

        foreach ($relatedPosts as $relatedPost) {
            $this->removeRelationships($relatedPost, $post);
        }
    }
}
