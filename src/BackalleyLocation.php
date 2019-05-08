<?php

namespace Backalley;

use Timber\Timber;
use Backalley\Html\SelectOptions;
use Backalley\DataFields\AddressFieldSet;

/**
 * @package Backalley-Starter
 */
class BackalleyLocation extends BackalleyConceptualPostType
{
    public static $id_prefix = "backalley--location_data--";
    public static $name_prefix = "backalley_location_data";
    public static $post_var = "backalley_location_data";

    public static function set_datasets()
    {
        Self::$datasets = [
            'address',
            'contact_info',
            'hours',
        ];
    }

    final public static function set_post_var()
    {
        Self::$post_var = Self::$post_var;
    }

    /**
     *
     */
    public static function render_address_fieldset($post)
    {
        AddressFieldSet::render($post);
    }

    /**
     *
     */
    public static function render_contact_info_fieldset($post)
    {
        $meta_prefix = BackAlley::$meta_key_prefix;

        $fields = [
            'phone' => [
                'attributes' => [
                    'type' => 'tel'
                ]
            ],
            'fax' => [
                'attributes' => [
                    'type' => 'tel'
                ]
            ],
            'email' => [
                'attributes' => [
                    'type' => 'email'
                ]
            ],
        ];

        foreach ($fields as $field => &$definition) {
            $definition['form_element'] = 'input';
            $definition['title'] = ucwords($field);

            $attrubutes = &$definition['attributes'];

            $attrubutes['value'] = get_post_meta($post->ID, "{$meta_prefix}{$post->post_type}_contact_info__{$field}", true) ?? '';
            $attrubutes['name'] = "backalley_location_data[contact_info][$field]";
            $attrubutes['id'] = "backalley--location_data--{$field}";
            $attrubutes['class'] = 'regular-text';
        }

        $fieldset = [
            'fieldset_title' => 'Contact Info',
            'fields' => $fields
        ];

        Self::generate_fieldset($fieldset, 3);
    }

    public static function render_hours_fieldset($post)
    {
        $post_id = $post->ID;
        $prefix = BackAlley::$meta_key_prefix;

        $days = [
            'sunday' => [],
            'monday' => [],
            'tuesday' => [],
            'wednesday' => [],
            'thursday' => [],
            'friday' => [],
            'saturday' => [],
        ];

        foreach ($days as $day => &$hours) {
            $hours['title'] = ucwords($day);

            $hours['hours']['open'] = [];
            $hours['hours']['close'] = [];

            foreach ($hours['hours'] as $hour => &$attr) {
                $attr['value'] = get_post_meta($post_id, $prefix . "{$post->post_type}_hours__{$day}_{$hour}", true);
                $attr['name'] = "backalley_location_data[hours][$day][$hour]";
            }
        }

        $twig_data['days'] = $days;

        Self::timber_render_fieldset($twig_data, 2);
    }

    /**
     *
     */
    public static function save_address($post_id, $post, $update, $fieldset = null, $raw_data = null)
    {
        AddressFieldSet::save_post_meta($post_id, $post, $update, $fieldset, $raw_data);
    }

    /**
     *
     */
    public static function save_contact_info($post_id, $post, $update, $fieldset = null, $raw_data = null)
    {
        $prefix = BackAlley::$meta_key_prefix;

        $instructions = [
            'phone' => [
                'check' => 'phone',
                'filter' => 'sanitize_text_field',
                'type' => 'post_meta',
                'item' => $post_id,
                'save' => $prefix . "{$post->post_type}_contact_info__phone"
            ],
            'fax' => [
                'check' => 'phone',
                'filter' => 'sanitize_text_field',
                'type' => 'post_meta',
                'item' => $post_id,
                'save' => $prefix . "{$post->post_type}_contact_info__fax"
            ],
            'email' => [
                'check' => 'email',
                'filter' => 'sanitize_email',
                'type' => 'post_meta',
                'item' => $post_id,
                'save' => $prefix . "{$post->post_type}_contact_info__email"
            ],
        ];

        $results = Saveyour::judge($instructions, $raw_data);
    }

    /**
     *
     */
    public static function save_hours($post_id, $post, $update, $fieldset = null, $raw_data = null)
    {
        $prefix = BackAlley::$meta_key_prefix;

        $validate_cb = apply_filters("backalley/validate/location/hours", "sanitize_text_field");
        $sanitize_cb = apply_filters("backalley/sanitize/location/hours", "sanitize_text_field");

        foreach ($raw_data as $day => $hours) {
            $day = sanitize_text_field($day);

            foreach ($hours as $hour => $time) {
                $hour = sanitize_text_field($hour);

                $meta_key = $prefix . "{$post->post_type}_hours__{$day}_{$hour}";

                $sanitized_data = filter_var(
                    $time,
                    FILTER_CALLBACK,
                    ['options' => $sanitize_cb]
                );

                $old_data = get_post_meta($post_id, $meta_key, true);

                if ($old_data !== $sanitized_data && !add_post_meta($post_id, $meta_key, $sanitized_data, true)) {
                    update_post_meta($post_id, $meta_key, $sanitized_data, $old_data);
                }
            }
        }
    }
}
