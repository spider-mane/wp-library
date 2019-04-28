<?php

/**
 * @package Backalley-Starter
 */

use Backalley\Backalley;

class Backalley_Restaurant_Location extends Backalley_Location
{
    public static $menu_item_relationship;
    public static $delivery_platforms;

    /**
     *
     */
    public static function super_set_menu_items($shadow_taxonomy, $menu_post_type)
    {
        Self::$menu_item_relationship = [
            $shadow_taxonomy,
            $menu_post_type
        ];
    }

    /**
     *
     */
    public static function super_set_delivery_platforms($delivery_platforms_taxonomy)
    {
        self::$delivery_platforms = $delivery_platforms_taxonomy;
    }

    /**
     * 
     */
    public static function set_datasets()
    {
        Parent::set_datasets();

        Self::$datasets = array_merge(Parent::$datasets, [
            'menu_items',
            'delivery_platforms'
        ]);
    }

    /**
     *
     */
    public static function render_delivery_platforms_fieldset($post)
    {
        $delivery_platforms = Self::$delivery_platforms;


        if (empty($delivery_platforms)) {
            return;
        }

        $meta_prefix = Backalley::$meta_key_prefix;
        $name_prefix = Self::$name_prefix;
        $id_prefix = Self::$id_prefix;

        // for the love of god do not uncomment the content in the brackets
        $fields = [
            // ...associated_platforms => ...[]
            // 'available_platforms' => [],
            // 'new_platform' => []
        ];

        $associated_platforms = get_the_terms($post->ID, $delivery_platforms) ? : [];

        $all_available_platforms = get_terms([
            'taxonomy' => $delivery_platforms,
            'hide_empty' => false
        ]);

        // create input for each associated platform
        foreach ($associated_platforms as $index => $platform) {
            $slug = $platform->slug;
            $title = htmlspecialchars_decode($platform->name);

            $meta_key = "{$meta_prefix}{$post->post_type}_delivery_platforms__{$slug}";

            $fields[$slug] = [
                'title' => $title,
                'form_element' => 'input',
                'container_id' => "backalley--platform_url__{$slug}--container",
                'attributes' => [
                    'type' => 'url',
                    'id' => Self::$id_prefix . "delivery-platforms--" . $slug,
                    'name' => Self::$name_prefix . "[delivery_platforms][{$slug}]",
                    'class' => 'large-text',
                    'value' => get_post_meta($post->ID, $meta_key, true),
                ],
                'wp_submit_button' => [
                    'text' => 'Remove',
                    'type' => 'delete small',
                    'name' => "backalley--platform_{$slug}--delete",
                    'wrap' => false,
                    'other_attributes' => [
                        'data-backalley-location-platform' => "backalley--platform_url__{$slug}--container",
                    ]
                ]
            ];
        }

        // // create templape for js dom manipulation
        // $fields['%platform_name%'] = [
        //     'title' => '%platform_title%',
        //     'form_element' => 'input',
        //     'container_id' => "backalley--platform_url__%platform_name%--container",
        //     'template' => true,
        //     'attributes' => [
        //         'type' => 'url',
        //         'value' => '',
        //         'class' => 'large-text',
        //         'data' => [
        //             "id-format=\"backalley--platform_url__%platform_name%--container\"",
        //         ],
        //         'disabled' => true,
        //         // 'hidden' => true,
        //     ],
        //     'wp_submit_button' => [
        //         'text' => 'Remove',
        //         'type' => 'delete small',
        //         'name' => "backalley--platform_'%platform_name%'--delete",
        //         'wrap' => false,
        //         'other_attributes' => [
        //             'data-backalley-location-platform' => "backalley--platform_url__{%platform_name%}--container"
        //         ]
        //     ]
        // ];

        // populate available platforms checklist items
        foreach ($all_available_platforms as $platform) {

            $slug = $platform->slug;
            $title = $platform->name;

            $list_items[] = [
                'attributes' => [
                    'type' => !in_array($platform, $associated_platforms) ? 'checkbox' : 'hidden',
                    'name' => "tax_input[{$delivery_platforms}][]",
                    'id' => Self::$id_prefix . $slug,
                    'value' => $slug,
                ],
                'label' => [
                    'content' => !in_array($platform, $associated_platforms) ? $title : '',
                ]
            ];
        }

        // available platforms field
        if (!empty($all_available_platforms)) {
            $fields['available_platforms'] = [
                'title' => 'Available Platforms',
                'form_element' => 'checklist',
                'container' => [
                    'attributes' => [
                        'class' => 'thing'
                    ]
                ],
                'items' => $list_items ?? [],
            ];
        }
        

        // // new platform field
        // $fields['new_platform'] = [
        //     'form_element' => 'input',
        //     'title' => 'Add New Platform',
        //     'attributes' => [
        //         'type' => 'text',
        //     ]
        // ];

        $fieldset = [
            'fieldset_title' => 'Delivery Platforms',
            'fields' => $fields
        ];

        Self::generate_fieldset($fieldset, 3);
    }

    /**
     * 
     */
    public static function render_menu_items_fieldset($post)
    {
        if (empty(Self::$menu_item_relationship)) {
            return;
        }

        $shadow_taxonomy = Self::$menu_item_relationship[0];
        $menu_post_type = Self::$menu_item_relationship[1];

        $prefixes = [
            'id' => Self::$id_prefix,
            'name' => Self::$name_prefix,
        ];

        $menu_items = get_posts([
            'post_type' => $menu_post_type,
            'numberposts' => -1,
            'orderby' => 'name'
        ]);

        $list_items = [];

        foreach ($menu_items as $menu_item) {
            $list_items[] = [
                'attributes' => [
                    'name' => $prefixes['name'] . "[menu_items][{$menu_item->ID}]",
                    'id' => $prefixes['id'] . 'menu-item--' . $menu_item->post_name,
                    'checked' => has_term("{$post->ID}", $shadow_taxonomy, $menu_item->ID) ? true : false,
                    'value' => '1',
                ],

                'label' => [
                    'content' => $menu_item->post_title,
                ],
            ];
        }

        $checklist = [
            'title' => 'Menu Items',
            'form_element' => 'checklist',
            'toggle' => true,
            'container' => [
                'attributes' => [
                    'id' => 'backalley--menu-items',
                    'class' => 'thing'
                ]
            ],
            'ul' => [
                'attributes' => []
            ],
            'items' => $list_items,
        ];

        $fieldset = [
            'fieldset_title' => 'Available Menu Items',
            'fields' => $checklist
        ];

        Self::generate_fieldset($fieldset, 3);
    }

    /**
     *
     */
    public static function save_delivery_platforms($post_id, $post, $update, $fieldset = null, $raw_data = null)
    {
        $meta_prefix = BackAlley::$meta_key_prefix;
        $taxonomy = Self::$delivery_platforms;

        // terms will have been processed by this point, so even if a new platform was added via post.php, it will be present
        $selected_platforms = wp_get_post_terms($post_id, $taxonomy);

        foreach ($selected_platforms as $index => $platform) {
            $selected_platforms[$platform->name] = $platform->slug;
            unset($selected_platforms[$index]);
        }

        // sanitize data
        $new_platforms = filter_var_array(
            $raw_data ?? [],
            FILTER_SANITIZE_URL
        );


        foreach ($new_platforms as $platform => $url) {
            /*
             * If the platform was newly added via the UI, the $platform will correspond to the name value for the term
             * instead of the slug, use the selected_platforms array of name=>slug pairs in order to retrieve the slug
             * if $platform does not correspond to a name, the term either existed prior to the page load or it is
             * invalid. The next step will deal with the latter possiblilty
             */
            $slug = $selected_platforms[htmlspecialchars($platform)] ?? $platform;

            
            /*
             * do not process anything that does not correspond to a term present in the selected_platforms
             * array any further
             */
            if (!in_array($slug, $selected_platforms)) {
                unset($new_platforms[$platform]);
                continue;
            }

            /**
             * gather old data for comparison
             */
            $meta_key = "{$meta_prefix}{$post->post_type}_delivery_platforms__{$slug}";
            $old_data = get_post_meta($post_id, $meta_key, true);


            /**
             * update the value in the database if it has been changed.
             * 
             * if delete button was clicked, remove corresponding platform from
             * the $new_platforms array and association with term
             */
            if ($url !== $old_data && !filter_has_var(INPUT_POST, "backalley--platform_{$platform}--delete")) {
                update_post_meta($post_id, $meta_key, $url);

            } elseif (filter_has_var(INPUT_POST, "backalley--platform_{$platform}--delete")) {
                delete_post_meta($post_id, $meta_key);
                wp_remove_object_terms($post_id, $slug, $taxonomy);
            }
        }
    }

    /**
     *
     */
    public static function save_menu_items($post_id, $post, $update, $fieldset = null, $raw_data = null)
    {
        $shadow_taxonomy = Self::$menu_item_relationship[0];

        $menu_items = filter_var(
            $raw_data,
            FILTER_CALLBACK,
            ['options' => 'sanitize_text_field']
        );

        $post_as_term = strval($post_id);

        foreach ($menu_items as $menu_item => $selected) {
            if ($selected) {
                /* 
                 * do not under any circunstances modify 4th argument. it must be set to true 
                 * in order to prevent completely rewriting terms of menu item
                 */
                wp_set_object_terms($menu_item, $post_as_term, $shadow_taxonomy, true);
            } elseif (!$selected) {
                wp_remove_object_terms($menu_item, $post_as_term, $shadow_taxonomy);
            }
        }
    }
}
