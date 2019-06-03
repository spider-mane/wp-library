<?php

namespace Backalley;

use function DeepCopy\deep_copy;


/**
 * Sorted Filtered Cloned Query
 * 
 * Simple helper class that clones an instance of WP_Query, filters the posts array based on a term relationship, then
 * sorts the filtered array based on a user defined display order.
 * 
 * Useful for gathering all posts intended for display in one query and visually compartmentalizing them by term in a
 * foreach loop rather than querying for posts in each iteration
 * 
 */
class SortedFilteredClonedQuery extends \WP_Query
{
    /**
     * 
     */
    public function __construct($term_id, $taxonomy, $query = null, $meta_key = null, $field = null)
    {
        if (!$query) {
            global $wp_query;
            $query = clone $wp_query;
            // $query = deep_copy($wp_query);
        } else {
            $query = clone $query;
            // $query = deep_copy($wp_query);
        }

        $filtered_posts = [];
        $post_positions = [];

        // $meta_key = $meta_key ?? "_term{$term_id}_display_position";

        $orderby_apex = "_term{$term_id}_display_position";
        $orderby_hierarchy = "_term{$term_id}_hierarchy_display_position";

        foreach ($query as $property => $value) {
            $this->$property = is_object($value) ? clone $value : $value;
        }

        foreach ($this->posts as $post) {
            if (!has_term($term_id, $taxonomy, $post->ID)) {
                continue;
            }

            $filtered_posts[] = clone $post;

            // $post_positions[$post->ID] = (int)get_post_meta($post->ID, $meta_key, true) ?? 0;
        }

        // var_dump($filtered_posts);
        // die;

        // $sorted_posts = Guctility_Belt::sort_objects_array($filtered_posts, $post_positions, 'ID');
        $sorted_posts = SortableObjectsBase::order_objects_array($filtered_posts, 'post', $orderby_apex, $orderby_hierarchy);

        $this->post = $sorted_posts[0] ?? null;
        $this->posts = $sorted_posts;
        // $this->posts = &$sorted_posts; // no idea why passing by reference works here but it do
        $this->post_count = count($sorted_posts);
    }
}
