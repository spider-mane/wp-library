<?php

/**
 * @package Backalley-Starter
 *
 * Creates shadow terms to correspond to each post in a given post type.
 *
 * @original Post_Shadow_Term
 *
 * The terms taxonomy can be provided or generated upon initialization.
 * The latter (auto generation) is recommended as certain capabilities
 * and disabling of certain auto generated ui features are crucial in
 * preventing any errors in a production environment.
 *
 * @param string $shadowed_post_type The post type to create mirror terms for
 * @param mixed $related_post_types Post types to assign to the shadow taxonomy
 * @param string $shadow_taxonomy Optional taxonomy to use as a post type shadow
 */

namespace WebTheory\Post2Post;

use Exception;

use function WebTheory\Leonidas\Helpers\json_encode_wp_safe;

class SomewhatRelatablePostType
{
    /**
     *
     */
    public $shadow_taxonomy;

    /**
     *
     */
    public $shadowed_post_type;

    /**
     *
     */
    public $stax_object;

    /**
     *
     */
    public $spt_object;

    /**
     *
     *
     * @param string $shadowed_post_type
     * @param mixed $related_post_types
     * @param string $shadow_taxonomy
     * @param array $labels
     */
    public function __construct($shadowed_post_type, $related_post_types = null, $shadow_taxonomy = null, $labels = null)
    {
        if (!isset($shadow_taxonomy)) {
            $this->create_shadow_taxonomy($shadowed_post_type, $related_post_types, $labels);
        } elseif (!empty($shadow_taxonomy) && taxonomy_exists($shadow_taxonomy)) {
            $this->set_shadow_taxonomy($shadow_taxonomy);
        }

        if (isset($related_post_types)) {
            $this->tinder_for_post_types($this->shadow_taxonomy, $related_post_types);
        }

        $this->puppeteer_shadow_terms($shadowed_post_type, $this->shadow_taxonomy);
    }

    /**
     * Sets the instances shadow taxonomy as the slug of taxonomy provided or
     * taxonomy generated by create_shadow_taxonomy().
     *
     * @param string $shadow_taxonomy The name of shadow taxonomy to create
     */
    public function set_shadow_taxonomy($shadow_taxonomy)
    {
        $this->shadow_taxonomy = $shadow_taxonomy;

        // $this->stax_object = get_taxonomy($this->shadow_taxonomy);
    }

    /**
     *
     */
    public function set_spt_object($shadowed_post_type)
    {
        $this->spt_object = get_post_type_object($shadowed_post_type);
    }

    /**
     *
     */
    public function create_shadow_taxonomy($shadowed_post_type, $related_post_types)
    {
        $spt_object = get_post_type_object($shadowed_post_type);

        $shadow_taxonomy = "_{$shadowed_post_type}_";

        $shadow_plural = $spt_object->label;
        $shadow_singular = $spt_object->labels->singular_name;


        /**
         * intercept registration of shadow taxonomy to bulk modify labels
         */
        add_action('registered_taxonomy', function ($taxonomy, $object_type, $taxonomy_object) use ($shadow_taxonomy, $shadow_singular) {
            if ($taxonomy !== $shadow_taxonomy) {
                return;
            }

            $labels = $taxonomy_object['labels'];

            $upper = "/Tag/";
            $lower = "/tag/";

            $shadow_singular_lower = strtolower($shadow_singular);

            foreach ($labels as $label => $value) {
                if (preg_match($upper, $value)) {
                    $labels->$label = preg_replace($upper, $shadow_singular, $value);
                } elseif (preg_match($lower, $value)) {
                    $labels->$label = preg_replace($lower, $shadow_singular, $value);
                }
            }
        }, null, 3);


        // Arguments and call to register shadow taxonomy
        $shadow_tax_rewrite = [
            'slug' => str_replace('_', '-', "{$shadowed_post_type}_shadow"),
            'with_front' => true,
            'hierarchical' => false,
        ];

        $shadow_tax_capabilities = [
            'manage_terms' => 'manage_options',
            'edit_terms' => 'manage_options',
            'delete_terms' => 'manage_options',
            'assign_terms' => 'edit_posts'
        ];

        $shadow_tax_args = [

            'description' => "Shadow taxonomy to allow posts to have a many to many relationship with {$shadow_plural}. Terms should not be manually added to this taxonomy unless to correct for a runtime error. By default, no clickable UI features are added for this taxonomy, as all processing should happen in the background and the default WordPress taxonomy metaboxes are not partucularly suitable for a \"taxonomy\" of this nature. Additionally, the ability to manage the terms available in this taxonomy is restricted to admins. Again, the sole purpose of this taxonomy is to create a psuedo relationship between {$shadow_plural} and other select post types",

            'label' => $shadow_plural,
            'hierarchical' => false,
            'public' => false,
            'publicly_queryable' => false,
            'meta_box_cb' => false,
            'rest_base' => '',
            'rewrite' => $shadow_tax_rewrite,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_menu' => false,
            'show_in_nav_menus' => true,
            'show_in_rest' => true,
            'show_in_quick_edit' => false,
            'show_tagcloud' => true,
            'capabilities' => $shadow_tax_capabilities
        ];

        register_taxonomy($shadow_taxonomy, $related_post_types, $shadow_tax_args);

        $this->set_shadow_taxonomy($shadow_taxonomy);
    }

    /**
     * Ensures $related_post_types are matched to the shadowed_post_type via
     * $shadow_taxonomy using the safeguard function register_taxonomy_for_object_type()
     * provided in the WordPress Core
     *
     * @param string $shadow_taxonomy
     * @param mixed $related_post_types
     *
     * @uses register_taxonomy_for_object_type()
     */
    public function tinder_for_post_types($shadow_taxonomy, $related_post_types)
    {
        if (!is_string($related_post_types) && !is_array($related_post_types)) {
            throw new Exception('$related_post_types argument passed to ' . __FUNCTION__ . '() must be string or array');
        }

        foreach ((array) $related_post_types as $post_type) {
            $registered = register_taxonomy_for_object_type($shadow_taxonomy, $post_type);
        }
    }

    /**
     *
     */
    public function puppeteer_shadow_terms($shadowed_post_type, $shadow_taxonomy)
    {
        add_action("save_post_{$shadowed_post_type}", $this->create_term_with_post($shadowed_post_type, $shadow_taxonomy), null, 3);

        add_action('delete_post', $this->delete_term_with_post($shadowed_post_type, $shadow_taxonomy));
    }

    /**
     *
     */
    public function create_term_with_post($shadowed_post_type, $shadow_taxonomy)
    {
        return function ($post_id, $post, $update) use ($shadowed_post_type, $shadow_taxonomy) {
            $return_if_conditions = [
                (bool) (!$update),
                (bool) (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE),
                // (bool)(!filter_has_var(INPUT_POST, 'original_post_status')),
            ];

            foreach ($return_if_conditions as $condition) {
                if ($condition) {
                    return;
                }
            }

            $post_id = strval($post_id);
            $post_title = $post->post_title;
            $post_status = $post->post_status;

            // create or update the term
            if (!term_exists($post_id, $shadow_taxonomy)) {

                // no need to continue if wp_error is returned
                if (is_wp_error(wp_insert_term($post_title, $shadow_taxonomy, ['slug' => $post_id]))) {
                    return;
                }
            } elseif ($post_status !== 'trash') {
                $term = get_term_by('slug', $post_id, $shadow_taxonomy);
                $term = $term->term_id;

                wp_update_term($term, $shadow_taxonomy, ['name' => $post_title]);
            }

            // Update the term meta regardless of circumstances
            $term_meta = json_encode_wp_safe([
                'shadow_term_of_post_of_type' => $post->post_type,
                'post_status' => $post_status,
            ]);


            if (!empty($term = get_term_by('slug', $post_id, $shadow_taxonomy))) {
                update_term_meta($term->term_id, "{$shadow_taxonomy}_term_data", $term_meta);
            }
        };
    }

    /**
     *
     */
    public function delete_term_with_post($shadowed_post_type, $shadow_taxonomy)
    {
        return function ($post_id) use ($shadowed_post_type, $shadow_taxonomy) {
            $post_type = get_post_type($post_id);

            if ($post_type !== $shadowed_post_type) {
                return;
            }

            $term = get_term_by('slug', strval($post_id), $shadow_taxonomy);
            $term = (int) $term->term_id;

            wp_delete_term($term, $shadow_taxonomy);
        };
    }


    /**
     * Updates
     */
    public function shadow_existing_posts($shadowed_post_type)
    {
        if ($shadowed_post_type !== $this->shadowed_post_type) {
            return;
        }

        $posts = [
            'post_type' => $shadowed_post_type,
            'numberposts' => -1
        ];

        $posts = get_posts($posts);

        // die(var_dump($posts));

        foreach ($posts as $post) {
            wp_update_post(['ID' => $post->ID]);
        }
    }


    /**
     * FUNCTIONALITY UNDER CONSTRUCTION DO NOT CALL
     *
     * Update the terms of a proided taxonomy's tax_term to the auto generated shadow taxonomy
     * As of now, only needs to be called once by the instance,
     * afterwards, the call can be safely removed from codebase, i dunno
     */
    public static function provided_tax_terms_tax_to_generated_tax($shadow_taxonomy)
    {
        global $wpdb;

        $terms = get_terms(['taxonomy' => $shadow_taxonomy]);

        foreach ($terms as $term) {
            $wpdb->update(
                $wpdb->prefix . 'term_taxonomy',
                ['taxonomy' => $this->shadow_taxonomy],
                ['term_taxonomy_id' => $term->term_id],
                ['%s'],
                ['%d']
            );
        }
    }
}
