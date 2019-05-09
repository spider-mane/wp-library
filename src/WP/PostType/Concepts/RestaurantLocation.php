<?php

namespace Backalley\WP\PostType\Concepts;

/**
 * @package Backalley-Starter
 */
class RestaurantLocation extends Location
{
    /**
     * 
     */
    public function set_meta_boxes()
    {
        parent::set_meta_boxes();

        $this->meta_boxes['fields']['info'] = array_merge($this->meta_boxes['fields']['info'], [
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
        ]);
    }
}
