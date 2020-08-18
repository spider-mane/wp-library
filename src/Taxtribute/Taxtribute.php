<?php

namespace WebTheory\Taxtribute;

use WP_Post;
use WP_Query;
use WP_Taxonomy;
use WP_Term;
use WP_Term_Query;

class Taxtribute
{
    /**
     * @var WP_Taxonomy
     */
    protected $taxonomy;

    /**
     * @var string
     */
    protected $metaKeyFormat = '%s__%s';

    /**
     *
     */
    public function __construct(string $taxonomy, ?string $metaKeyFormat = null)
    {
        $this->taxonomy = $taxonomy;
        $metaKeyFormat && $this->metaKeyFormat = $metaKeyFormat;
    }

    /**
     * Get the value of taxonomy
     *
     * @return string
     */
    public function getTaxonomy(): string
    {
        return $this->taxonomy;
    }

    /**
     * Get the value of metaKey
     *
     * @return string
     */
    public function getMetaKey(string $attribute): string
    {
        return sprintf($this->metaKeyFormat, $this->taxonomy, $attribute);
    }

    /**
     *
     */
    public function getGetAttributesQuery(): WP_Term_Query
    {
        return new WP_Term_Query([
            'taxonomy' => $this->taxonomy,
            'hierarchical' => false
        ]);
    }

    /**
     *
     */
    public function getPostAttributesQuery(int $postId)
    {
        return new WP_Term_Query([
            'taxonomy' => $this->taxonomy,
            'hierarchical' => false,
            'object_ids' => $postId
        ]);
    }

    /**
     *
     */
    public function getPostsWithAttributeQuery(string $attribute): WP_Query
    {
        return new WP_Query([
            'post_type' => 'any',
            'tax_query' => [[
                'taxonomy' => $this->taxonomy,
                'terms' => $attribute,
            ]]
        ]);
    }

    /**
     * @return WP_Term[]
     */
    public function getAttributesAssignedToPost(int $postId): array
    {
        return $this->getPostAttributesQuery($postId)->get_terms();
    }

    /**
     * @return WP_Term[]
     */
    public function getAttributes(): array
    {
        return $this->getGetAttributesQuery()->get_terms();
    }

    /**
     * @return WP_Post[]
     */
    public function getPostsWithAttribute($attribute): array
    {
        return $this->getPostsWithAttributeQuery($attribute)->get_posts();
    }

    /**
     *
     */
    public function getAttribute(string $attribute): WP_Term
    {
        return get_term_by('slug', $attribute, $this->taxonomy);
    }

    /**
     *
     */
    public function getAttributeName(string $attribute): string
    {
        return $this->getAttribute($attribute)->name;
    }

    /**
     *
     */
    public function attributeExists(string $attribute): bool
    {
        return term_exists($attribute, $this->taxonomy);
    }

    /**
     *
     */
    public function hasAttribute(int $post, string $attribute): bool
    {
        return has_term($attribute, $this->taxonomy, $post);
    }

    /**
     *
     */
    public function assignAttributesToPost(int $post, string ...$attributes): void
    {
        wp_set_object_terms($post, $attributes, $this->taxonomy, true);
    }

    /**
     *
     */
    public function unassignAttributesFromPost(int $post, string ...$attributes): void
    {
        wp_remove_object_terms($post, $attributes, $this->taxonomy);
    }

    /**
     *
     */
    public function setValue(int $post, string $attribute, $value): void
    {
        $this->assignAttributesToPost($post, $attribute);
        update_post_meta($post, $this->getMetaKey($attribute), $value);
    }

    /**
     *
     */
    public function getValue(int $post, string $attribute)
    {
        return get_post_meta($post, $this->getMetaKey($attribute)) ?: null;
    }

    /**
     *
     */
    public function unsetValue(int $post, string $attribute): void
    {
        $this->unassignAttributesFromPost($post, $attribute);
        delete_post_meta($post, $this->getMetaKey($attribute));
    }
}
