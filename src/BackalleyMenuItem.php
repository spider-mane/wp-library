<?php

/**
 * @package Backalley-Starter
 */

namespace Backalley;

class BackalleyMenuItem extends BackalleyConceptualPostType
{
    public static $id_prefix = "backalley--menu-item--";
    public static $name_prefix = "backalley_menu_item_data";

    public static $locations_relationship;

    /**
     * 
     */
    public static function set_post_var()
    {
        Self::$post_var = Self::$name_prefix;
    }

    /**
     * 
     */
    public static function super_set_locations_available($shadow_taxonomy, $locations_post_type)
    {
        Self::$locations_relationship = [
            $shadow_taxonomy,
            $locations_post_type
        ];
    }

    /**
     * 
     */
    public static function render_description_fieldset($post, $metabox)
    {
        $post_type = $post->post_type;
        $meta_prefix = Backalley::$meta_key_prefix;


        $description = [
            'title' => 'Description',
            'form_element' => 'textarea',
            'content' => get_post_meta($post->ID, $meta_prefix . "{$post_type}_description", true),
            'attributes' => [
                'class' => 'large-text',
                'id' => Self::$id_prefix . "description",
                'name' => Self::$name_prefix . "[description]",
            ]
        ];

        $fieldset = [
            'fieldset_title' => 'Description',
            'fields' => $description
        ];

        Self::generate_fieldset($fieldset, 3);
    }

    /**
     * 
     */
    public static function render_price_fieldset($post)
    {
        $post_type = $post->post_type;
        $meta_prefix = Backalley::$meta_key_prefix;

        $price = [
            'title' => 'USD',
            'form_element' => 'input',
            'attributes' => [
                'type' => 'text',
                'id' => Self::$id_prefix . "price",
                'name' => Self::$name_prefix . "[price]",
                'class' => 'small-text',
                'value' => get_post_meta($post->ID, $meta_prefix . "{$post_type}_price", true),
            ]
        ];

        $fieldset = [
            'fieldset_title' => 'Price',
            'fields' => $price
        ];

        Self::generate_fieldset($fieldset, 3);
    }

    /**
     * 
     */
    public static function render_locations_available_fieldset($post)
    {
        if (empty(Self::$locations_relationship)) {
            return;
        }

        $shadow_taxonomy = Self::$locations_relationship[0];
        $locations_post_type = Self::$locations_relationship[1];

        $locations = get_posts([
            'post_type' => $locations_post_type,
            'numberposts' => -1,
            'orderby' => 'name'
        ]);

        $list_items = [];

        foreach ($locations as $location) {
            $list_items[] = [
                'attributes' => [
                    'name' => "tax_input[{$shadow_taxonomy}][]",
                    'id' => Self::$id_prefix . 'location--' . $location->post_name,
                    'checked' => has_term("{$location->ID}", $shadow_taxonomy, $post->ID) ? true : false,
                    'value' => "{$location->ID}",
                ],

                'label' => [
                    'content' => $location->post_title,
                    'attributes' => [
                        'class' => 'selectit'
                    ]
                ],
            ];
        }

        $checklist = [
            'title' => 'Locations Available',
            'form_element' => 'checklist',
            'clear_control' => "tax_input[{$shadow_taxonomy}][]",
            'container' => [
                'attributes' => [
                    'id' => 'backalley--locations-available',
                    'class' => 'thing'
                ]
            ],
            'items' => $list_items,
        ];

        $fieldset = [
            'fieldset_title' => 'Locations Available',
            'fields' => $checklist
        ];

        Self::generate_fieldset($fieldset, 3);
    }

    /**
     * 
     */
    public static function save_description($post_id, $post, $update, $fieldset = null, $raw_data = null)
    {
        $meta_prefix = Backalley::$meta_key_prefix;
        $meta_key = "{$meta_prefix}{$post->post_type}_description";

        $old_description = get_post_meta($post_id, $meta_key, true);

        $new_description = sanitize_textarea_field($raw_data);

        if ($new_description !== $old_description) {
            update_post_meta($post->ID, $meta_key, $new_description);
        }
    }

    /**
     * 
     */
    public static function save_price($post_id, $post, $update, $fieldset = null, $raw_data = null)
    {
        $meta_prefix = Backalley::$meta_key_prefix;
        $meta_key = "{$meta_prefix}{$post->post_type}_price";

        $old_price = get_post_meta($post_id, $meta_key, true);

        $new_price = sanitize_text_field($raw_data);

        if ($new_price !== $old_price) {
            update_post_meta($post_id, $meta_key, $new_price);
        }
    }
}
