<?php

namespace WebTheory\Post2Post;

use WP_Post;
use WP_Post_Type;
use WP_Taxonomy;
use WebTheory\Leonidas\Taxonomy\Taxonomy;
use WebTheory\Post2Post\Exceptions\InvalidContextArgumentException;

use function WebTheory\Leonidas\Helpers\json_encode_wp_safe;

class Model
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var WP_Post_Type
     */
    protected $relatablePostType;

    /**
     * @var WP_Post_Type
     */
    protected $relatedPostType;

    /**
     * @var WP_Taxonomy
     */
    protected $shadowTaxonomy;

    /**
     *
     */
    public function __construct(string $name, WP_Post_Type $relatablePostType, WP_Post_Type $relatedPostType)
    {
        $this->name = $name;
        $this->relatablePostType = $relatablePostType;
        $this->relatedPostType = $relatedPostType;
        $this->shadowTaxonomy = $this->createShadowTaxonomy();

        $this->shadowRelatablePostTypePosts();
    }

    /**
     * Get the value of name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the value of relatablePostType
     *
     * @return WP_Post_Type
     */
    public function getRelatablePostType(): WP_Post_Type
    {
        return $this->relatablePostType;
    }

    /**
     * Get the value of relatedPostType
     *
     * @return WP_Post_Type
     */
    public function getRelatedPostType(): WP_Post_Type
    {
        return $this->relatedPostType;
    }

    /**
     * Get the value of shadowTaxonomy
     *
     * @return WP_Taxonomy
     */
    public function getShadowTaxonomy(): WP_Taxonomy
    {
        return $this->shadowTaxonomy;
    }

    /**
     *
     */
    public function getRelatedPostTypeName()
    {
        return $this->relatedPostType->name;
    }

    /**
     *
     */
    public function getRelatablePostTypeName()
    {
        return $this->relatablePostType->name;
    }

    /**
     *
     */
    public function getShadowTaxonomyName()
    {
        return $this->shadowTaxonomy->name;
    }

    /**
     *
     */
    public function getRelatedPosts(WP_Post $post)
    {
        switch ($post->post_type) {
            case $this->getRelatablePostTypeName():
                $posts = $this->getRelatablePostRelationships($post);
                break;

            case $this->getRelatedPostTypeName():
                $posts = $this->getRelatedPostRelationships($post);
                break;
        }

        return $posts;
    }

    /**
     *
     */
    protected function getRelatablePostRelationships(WP_Post $post)
    {
        return get_posts([
            'post_type' => $this->getRelatedPostTypeName(),
            'posts_per_page' => -1,
            'suppress_filters' => false,
            'tax_query' => [[
                'taxonomy' => $this->getShadowTaxonomyName(),
                'terms' => (string) $post->ID,
                'field' => 'slug',
                'include_children' => false,
            ]]
        ]);
    }

    /**
     *
     */
    protected function getRelatedPostRelationships(WP_Post $post)
    {
        $postIds = get_terms([
            'taxonomy' => $this->getShadowTaxonomyName(),
            'fields' => 'ids',
            'object_ids' => $post->ID,
            'hierarchical' => false,
        ]);

        return !$postIds ? [] : get_posts([
            'post_type' => $this->getRelatablePostTypeName(),
            'post_in' => $postIds,
            'posts_per_page' => -1,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);
    }

    /**
     *
     */
    public function getPostsFor(string $context)
    {
        switch ($context) {
            case 'relatable':
                $posts = $this->getRelatedPostTypePosts();
                break;

            case 'related':
                $posts = $this->getRelatablePostTypePosts();
                break;

            default:
                throw new InvalidContextArgumentException();
        }

        return $posts;
    }

    /**
     *
     */
    public function getRelatedPostTypePosts()
    {
        return get_posts([
            'post_type' => $this->getRelatedPostTypeName(),
            'posts_per_page' => -1,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);
    }

    /**
     *
     */
    public function getRelatablePostTypePosts()
    {
        return get_posts([
            'post_type' => $this->getRelatablePostTypeName(),
            'posts_per_page' => -1,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);
    }

    /**
     *
     */
    public function setPostRelationships(WP_Post $post, string ...$relationships)
    {
        switch ($post->post_type) {
            case $this->getRelatablePostTypeName():
                $results = $this->setRelatableToRelatedRelationships($post, ...$relationships);
                break;

            case $this->getRelatedPostTypeName():
                $results = $this->setRelatedToRelatableRelationships($post, ...$relationships);
                break;
        }

        return $results;
    }

    /**
     *
     */
    protected function setRelatedToRelatableRelationships(WP_Post $post, string ...$relationships)
    {
        wp_set_object_terms(
            $post->ID,
            $relationships,
            $this->getShadowTaxonomyName(),
            true //! must be set to true in order to avoid completely rewriting relationships of related post
        );
    }

    /**
     *
     */
    protected function setRelatableToRelatedRelationships(WP_Post $post, string ...$relationships)
    {
        foreach ($relationships as $related) {
            $this->setPostRelationships(get_post($related), $post->ID);
        }
    }

    /**
     *
     */
    public function unsetPostRelationships(WP_Post $post, string ...$relationships)
    {
        switch ($post->post_type) {
            case $this->getRelatablePostTypeName():
                $results = $this->unsetRelatablePostRelationships($post, ...$relationships);
                break;

            case $this->getRelatedPostTypeName():
                $results = $this->unsetRelatedPostRelationships($post, ...$relationships);
                break;
        }

        return $results;
    }

    /**
     *
     */
    protected function unsetRelatedPostRelationships(WP_Post $post, string ...$relationships)
    {
        return wp_remove_object_terms($post->ID, $relationships, $this->getShadowTaxonomyName());
    }

    /**
     *
     */
    protected function unsetRelatablePostRelationships(WP_Post $post, string ...$relationships)
    {
        foreach ($relationships as $related) {
            $this->unsetPostRelationships(get_post($related), $post->ID);
        }
    }

    /**
     *
     */
    protected function shadowRelatablePostTypePosts()
    {
        add_action("save_post_{$this->getRelatablePostTypeName()}", [$this, 'createTermWithPost'], null, 3);
        add_action('delete_post', [$this, 'deleteTermWithPost']);
    }

    /**
     *
     */
    public function createTermWithPost(int $postId, WP_Post $post, bool $update)
    {
        if (!$update || defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $postId = (string) $postId;
        $postType = $post->post_type;
        $postTitle = $post->post_title;
        $postStatus = $post->post_status;
        $shadowTaxonomy = $this->getShadowTaxonomyName();

        // create or update the term
        if (!term_exists($postId, $shadowTaxonomy)) {

            // no need to continue if wp_error is returned
            if (is_wp_error(wp_insert_term($postTitle, $shadowTaxonomy, ['slug' => $postId]))) {
                return;
            }
        } elseif ($postStatus !== 'trash') {
            $term = get_term_by('slug', $postId, $shadowTaxonomy);
            $term = $term->term_id;

            wp_update_term($term, $shadowTaxonomy, ['name' => $postTitle]);
        }

        // Update the term meta regardless of circumstances
        $term_meta = json_encode_wp_safe([
            'shadow_term_of_post_of_type' => $postType,
            'postStatus' => $postStatus,
        ]);

        if (!empty($term = get_term_by('slug', $postId, $shadowTaxonomy))) {
            update_term_meta($term->term_id, "{$shadowTaxonomy}_term_data", $term_meta);
        }
    }

    /**
     *
     */
    public function deleteTermWithPost($postId)
    {
        $postType = get_post_type($postId);
        $shadowTaxonomy = $this->getShadowTaxonomyName();

        if ($postType !== $this->getRelatablePostTypeName()) {
            return;
        }

        $term = get_term_by('slug', strval($postId), $shadowTaxonomy);
        $term = (int) $term->term_id;

        wp_delete_term($term, $shadowTaxonomy);
    }

    /**
     *
     */
    protected function createShadowTaxonomy()
    {
        $taxonomy = new Taxonomy($this->name, $this->getRelatedPostTypeName());

        $taxonomy->setLabels([
            'singular_name' => $this->relatablePostType->labels->singular_name,
            'name' => $this->relatablePostType->labels->name
        ]);

        $taxonomy->setRewrite([
            'slug' => str_replace('_', '-', $this->name),
            'with_front' => true,
            'hierarchical' => false,
        ]);

        $capabilities = [
            'manage_terms' => 'manage_options',
            'edit_terms' => 'manage_options',
            'delete_terms' => 'manage_options',
            'assign_terms' => 'edit_posts'
        ];

        $description = "Shadow taxonomy to simulate many to many relationship between posts. Do not add terms directly.";

        $taxonomy->setArgs([
            'hierarchical' => false,
            'public' => false,
            'publicly_queryable' => false,
            'meta_box_cb' => false,
            'rest_base' => '',
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_menu' => false,
            'show_in_nav_menus' => true,
            'show_in_rest' => true,
            'show_in_quick_edit' => false,
            'show_tagcloud' => true,
            'capabilities' => $capabilities,
            'description' => $description,
        ]);

        return $taxonomy->register()->getRegisteredTaxonomy();
    }

    /**
     *
     */
    public function register()
    {
        Repository::addRelationship($this);
    }
}
