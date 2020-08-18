<?php

namespace WebTheory\Post2Post;

use WP_Post;
use WP_Post_Type;
use WP_Taxonomy;
use WP_Term;
use WebTheory\Leonidas\Taxonomy\Taxonomy;

class SomewhatRelatablePostType
{
    /**
     * @var WP_Post_Type
     */
    protected $postType;

    /**
     * @var WP_Taxonomy
     */
    protected $taxonomy;

    /**
     * @var string[]
     */
    protected $relatablePostTypes = [];

    /**
     *
     */
    protected const SHADOW_TAXONOMY_FORMAT = '&shadow->%s';

    /**
     *
     */
    public function __construct(WP_Post_Type $postType, string ...$relatablePostTypes)
    {
        $this->postType = $postType;
        $this->relatablePostTypes = $relatablePostTypes;
        $this->taxonomy = $this->createShadowTaxonomy();

        $this->mapTermsToPosts();
    }

    /**
     *
     */
    public function getName()
    {
        return static::getShadowName($this->postType->name);
    }

    /**
     *
     */
    protected function getRelatablePostTypeNames(): array
    {
        return array_map(function (WP_Post_Type $postType) {
            return $postType->name;
        }, $this->relatablePostTypes);
    }

    /**
     *
     */
    protected function createShadowTaxonomy(): WP_Taxonomy
    {
        $taxonomy = new Taxonomy($this->getName(), $this->relatablePostTypes);

        $taxonomy->setLabels([
            'singular_name' => $this->postType->labels->singular_name,
            'name' => $this->postType->labels->name
        ]);

        // $taxonomy->setRewrite([
        //     'slug' => str_replace('_', '-', $this->getName()),
        //     'with_front' => true,
        //     'hierarchical' => false,
        // ]);

        $taxonomy->setCapabilities([
            'manage_terms' => 'edit_posts',
            'edit_terms' => 'edit_posts',
            'delete_terms' => 'edit_posts',
            'assign_terms' => 'edit_posts'
        ]);

        $taxonomy->setDescription("DO NOT ADD TERMS DIRECTLY!. This taxonomy shadows the post type \"{$this->postType->name}\" in order to facilitate relationships between its own posts and those of other post types by maintaining parity between each term and its corresponding post.");

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
        ]);

        return $taxonomy->register()->getRegisteredTaxonomy();
    }

    /**
     *
     */
    protected function getShadowTermName(WP_Post $post): string
    {
        return $post->post_title;
    }

    /**
     *
     */
    protected function getShadowTermSlug(WP_Post $post): string
    {
        return (string) $post->ID;
    }

    /**
     *
     */
    protected function getShadowTermPostId(WP_Term $term): int
    {
        return (int) $term->slug;
    }

    /**
     *
     */
    protected function getTermPost(WP_Term $term): WP_Post
    {
        return get_post($this->getShadowTermPostId($term));
    }

    /**
     *
     */
    protected function getPostTerm(WP_Post $post): WP_Term
    {
        return get_term_by('slug', $this->getShadowTermSlug($post), $this->getName());
    }

    /**
     *
     */
    protected function createShadowTerm(WP_Post $post): void
    {
        $args = ['slug' => $this->getShadowTermSlug($post)];

        wp_insert_term($this->getShadowTermName($post), $this->getName(), $args);
    }

    /**
     *
     */
    protected function updateShadowTerm(WP_Post $post): void
    {
        $term = $this->getPostTerm($post);
        $args = ['name' => $this->getShadowTermName($post)];

        wp_update_term($term->term_id, $this->getName(), $args);
    }

    /**
     *
     */
    protected function deleteShadowTerm(WP_Post $post): void
    {
        $term = $this->getPostTerm($post);

        wp_delete_term($term->term_id, $this->getName());
    }

    /**
     *
     */
    protected function shadowTermExists(WP_Post $post): bool
    {
        return (bool) term_exists($this->getShadowTermSlug($post), $this->getName());
    }

    /**
     *
     */
    protected function shadowTermIsUpdated(WP_Term $term): bool
    {
        $post = $this->getTermPost($term);

        return $term->name === $post->post_title;
    }

    /**
     *
     */
    protected function mapTermsToPosts(): void
    {
        add_action('delete_post', $this->deleteTermWithPost());
        add_action("added_term_relationship", $this->updateTermOnEntry(), null, 3);
        add_action("save_post_{$this->postType->name}", $this->updateTermWithPost(), null, 3);
        add_action("registered_taxonomy_for_object_type", $this->appendNewPostType(), null, 2);
    }

    /**
     *
     */
    protected function appendNewPostType(): callable
    {
        return function ($taxonomy, $postType) {
            if (
                $taxonomy === $this->getName()
                && !in_array($postType, $this->relatablePostTypes)
            ) {
                $this->relatablePostTypes[] = $postType;
            }
        };
    }

    /**
     *
     */
    protected function updateTermOnEntry(): callable
    {
        return function ($postId, $ttId, $taxonomy) {
            if ($taxonomy !== $this->getName()) {
                return;
            }

            $term = get_term_by('term_taxonomy_id', $ttId, $taxonomy);
            $post = $this->getTermPost($term);

            if (!$this->shadowTermIsUpdated($term)) {
                $this->updateShadowTerm($post);
            }
        };
    }

    /**
     *
     */
    protected function updateTermWithPost(): callable
    {
        return function (int $postId, WP_Post $post, bool $update) {
            if (!$update || defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            if ($this->shadowTermExists($post)) {
                $this->updateShadowTerm($post);
            } else {
                $this->createShadowTerm($post);
            }
        };
    }

    /**
     *
     */
    protected function deleteTermWithPost(): callable
    {
        return function ($postId) {
            $post = get_post($postId);

            if ($post->post_type !== $this->postType->name) {
                return;
            }

            $this->deleteShadowTerm($post);
        };
    }

    /**
     *
     */
    protected static function getShadowName(string $postType): string
    {
        return sprintf(static::SHADOW_TAXONOMY_FORMAT, $postType);
    }

    /**
     *
     */
    public static function getShadow(string $postType): WP_Taxonomy
    {
        return get_taxonomy(static::getShadowName($postType));
    }

    /**
     *
     */
    public static function shadowExists(string $postType): bool
    {
        return taxonomy_exists(static::getShadowName($postType));
    }

    /**
     *
     */
    public static function buildNewRelationship(string $basePostType, string $relatedPostType)
    {
        register_taxonomy_for_object_type(static::getShadowName($basePostType), $relatedPostType);
    }
}
