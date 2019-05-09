<?php

namespace Backalley\WP\PostType\Concepts;

/**
 * @package Backalley-Starter
 */
class Location extends ConceptualPostType
{
    /**
     * 
     */
    public function set_meta_boxes()
    {
        $this->meta_boxes['info'] = [
            'id' => 'backalley_location_data',
            'title' => 'Location Info',
            'screen' => 'ba_location',
            'context' => 'normal',
            'priority' => 'high',
            'fields' => [
                'address' => [
                    'field' => 'address_fieldset',
                ],
                'contact_info' => [
                    'field' => 'contact_info_fieldset',
                ],
                'hours' => [
                    'field' => 'hours_fieldset',
                ],
                'menu_items' => [
                    'field' => 'post_relationship_checklist',
                    'name' => 'menu_items',
                    'title' => 'Available Menu Items',
                    'relatable' => 'ba_location',
                    'related' => 'ba_menu_item',
                    'connection' => '_ba_location_'
                ],
                'delivery_platforms' => [
                    'field' => 'attribute_taxonomy_fieldset',
                    'name' => 'delivery_platforms',
                    'title' => 'Delivery Platforms',
                    'terms_title' => 'Available Platforms',
                    'attribute_taxonomy' => 'ba_delivery_platforms',
                    'meta_key_format' => "delivery_platforms__%s",
                    'sanitize' => 'url',
                    'validate' => 'url',
                    'input_type' => 'url',
                ],
            ]
        ];
    }

    /**
     * 
     */
    public function set_list_table_columns()
    {
        //
    }
}
