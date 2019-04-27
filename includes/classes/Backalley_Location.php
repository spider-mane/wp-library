<?php

/**
 *
 */

use Timber\Timber;

class Backalley_Location extends Backalley_Conceptual_Post_Type
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
        $post_id = $post->ID;
        $prefix = BackAlley::$meta_key_prefix;

        $fields = [
            'street' => [],
            'city' => [],
            'state' => [
                'form_element' => 'select',
                'options' => array_merge(['' => 'Select State'], backalley_us_states()),
                'selected' => get_post_meta($post_id, $prefix . "{$post->post_type}_address__state", true)
            ],
            'zip' => [],
            'complete' => [
                'attributes' => [
                    'disabled' => true
                ]
            ],
            'geo' => [
                'attributes' => [
                    'disabled' => true
                ]
            ],
        ];

        foreach ($fields as $field => &$definition) {
            $definition['title'] = ucwords(str_replace('_', ' ', $field));

            // add attributes array if not there
            if (!array_key_exists('attributes', $definition)) {
                $definition['attributes'] = [];
            }
            $attributes = &$definition['attributes'];

            if ($field !== 'state') {
                $definition['form_element'] = 'input';
                $attributes['type'] = 'text';
                $attributes['value'] = get_post_meta($post_id, $prefix . "{$post->post_type}_address__{$field}", true) ?? '';
            }

            $attributes['name'] = "backalley_location_data[address][$field]";
            $attributes['id'] = "backalley--location_data--{$field}";
            $attributes['class'] = 'regular-text';

            // make json stored data presentable
            if ($field === 'geo' && !empty($attributes['value'])) {
                $attributes['value'] = htmlspecialchars($attributes['value']);
            }
        }

        $fieldset = [
            'fieldset_title' => 'Address',
            'fields' => $fields
        ];

        Self::generate_fieldset($fieldset, 3);
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
                    'type' => 'phone'
                ]
            ],
            'fax' => [
                'attributes' => [
                    'type' => 'phone'
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
        $post_type = $post->post_type;
        $prefix = BackAlley::$meta_key_prefix;

        $updated = false;

        $expected_data = apply_filters('backalley/location/save/address/whitelist', [
            'street',
            'city',
            'state',
            'zip'
        ]);

        foreach ($expected_data as $field) {
            $meta_key = $prefix . "{$post_type}_address__{$field}";

            $old_data = get_post_meta($post_id, $meta_key, true);
            $sanitized_data[$field] = sanitize_text_field($raw_data[$field]);


            if ($old_data !== $sanitized_data[$field]) {
                if ($updated === false) {
                    $updated = true;
                }

                update_post_meta($post_id, $meta_key, $sanitized_data[$field], $old_data);
            }
        }

        if ($updated === true) {
            $complete_address = backalley_concat_address(
                $sanitized_data['street'],
                $sanitized_data['city'],
                $sanitized_data['state'],
                $sanitized_data['zip']
            );

            update_post_meta($post_id, $prefix . "{$post_type}_address__complete", $complete_address);

            if (isset(Backalley::$api_keys['google_maps'])) {
                $coordinates = backalley_request_google_geocode($complete_address, Backalley::$api_keys['google_maps']);

                update_post_meta($post_id, "{$post_type}_address__geo", $coordinates);
            }
        }
    }

    /**
     *
     */
    public static function save_contact_info($post_id, $post, $update, $fieldset = null, $raw_data = null)
    {
        $prefix = BackAlley::$meta_key_prefix;

        $sanitized_data = [
            'phone' => [
                'filter' => FILTER_CALLBACK,
                'options' => 'sanitize_text_field'
            ],
            'fax' => [
                'filter' => FILTER_CALLBACK,
                'options' => 'sanitize_text_field'
            ],
            'email' => [
                'filter' => FILTER_CALLBACK,
                'options' => 'sanitize_email'
            ],
        ];

        foreach ($sanitized_data as $field => &$args) {
            $args['options'] = apply_filters("backalley/sanitize/location/{$field}", $args['options']);
        }

        $sanitized_data = filter_var_array($raw_data, $sanitized_data);

        foreach ($sanitized_data as $field => $new_data) {
            $meta_key = $prefix . "{$post->post_type}_contact_info__{$field}";
            $old_data = get_post_meta($post_id, $meta_key, true);

            if ($old_data !== $new_data) {
                update_post_meta($post_id, $meta_key, $new_data);
            }
        }
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
