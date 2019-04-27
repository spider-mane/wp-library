<?php

/**
 * 
 */

class Backalley_Press_Review extends Backalley_Conceptual_Post_Type
{
    public static $query_var = "backalley_press_review_data";
    public static $id_prefix = 'backelley--press-review--';

    /**
     * 
     */
    public static function set_post_var()
    {
        Self::$post_var = Self::$query_var;
    }

    /**
     * 
     */
    public static function set_meta_boxes()
    {
        $this->meta_boxes = [
            'article' => [
                'name' => 'article',
                'title' => 'Article',
                'context' => 'normal',
                'fields' => 'review'
            ],
            'info' => [
                'name' => 'publication_info',
                'title' => 'Publisher Info',
                'context' => 'normal',
                'fields' => 'publication_info'
            ]
        ];
    }

    /**
     * 
     */
    public static function render_review_fieldset($post, $metabox)
    {
        $meta_prefix = BackAlley::$meta_key_prefix;
        $post_type = $post->post_type;

        $fields = [
            'subtitle' => [
                'title' => 'Subtitle',
                'form_element' => 'input',
                'attributes' => [
                    'type' => 'text',
                    'value' => get_post_meta($post->ID, "{$meta_prefix}{$post_type}_subtitle", true),
                ]
            ],
            'excerpt' => [
                'title' => 'Excerpt',
                'form_element' => 'textarea',
                // 'content' => get_post_field('excerpt', $post->ID, 'raw'),
                'content' => get_post_meta($post->ID, "{$meta_prefix}{$post_type}_excerpt", true),
                'attributes' => [
                    // 'id' => Self::$id_prefix . 'excerpt',
                    // 'class' => 'large-text',
                    'rows' => 5,
                    // 'name' => 'excerpt',
                ]
            ],
            'content' => [
                'title' => 'Content',
                'form_element' => 'textarea',
                'content' => get_post_meta($post->ID, "{$meta_prefix}{$post_type}_content", true),
                'attributes' => [
                    'rows' => 30
                ]
            ],
            'link' => [
                'title' => 'Link',
                'form_element' => 'input',
                'attributes' => [
                    'type' => 'url',
                    'value' => get_post_meta($post->ID, "{$meta_prefix}{$post_type}_link", true),
                ]
            ],
        ];

        foreach ($fields as $field => &$definiton) {
            $attributes = &$definiton['attributes'];

            // if ($field !== 'excerpt') {
            $attributes['name'] = Self::$query_var . "[review][{$field}]";
            $attributes['id'] = Self::$id_prefix . $field;
            $attributes['class'] = 'large-text';
            // }

            unset($definiton, $attributes);
        }

        $fieldset = [
            'fieldset_title' => 'Review',
            'fields' => $fields,
        ];

        Self::generate_fieldset($fieldset, 3);
    }

    /**
     * 
     */
    public static function render_publication_info_fieldset($post, $metabox)
    {
        $meta_prefix = BackAlley::$meta_key_prefix;
        $post_type = $post->post_type;

        $fields = [
            'publication' => [
                'title' => 'Publication',
                'form_element' => 'input',
                'attributes' => [
                    'type' => 'text',
                    'class' => 'regular-text'
                ]
            ],
            'date_published' => [
                'title' => 'Date Published',
                'form_element' => 'input',
                'attributes' => [
                    'type' => 'date'
                ]
            ],
            'author' => [
                'title' => 'Review Author',
                'form_element' => 'input',
                'attributes' => [
                    'type' => 'text',
                    'class' => 'regular-text'
                ]
            ],
        ];

        foreach ($fields as $field => &$info) {
            $attributes = &$info['attributes'];

            $attributes['value'] = get_post_meta($post->ID, "{$meta_prefix}{$post->post_type}_{$field}", true);
            $attributes['name'] = Self::$query_var . "[publication_info][{$field}]";
            $attributes['id'] = Self::$id_prefix . $field;
        }

        $fieldset = [
            'fieldset_title' => "Publication Info",
            'fields' => $fields
        ];

        Self::generate_fieldset($fieldset, 3);
    }

    /**
     * 
     */
    public static function save_review($post_id, $post, $update, $fieldset = null, $raw_data = null)
    {
        $meta_prefix = BackAlley::$meta_key_prefix;

        $sanitized_data = [
            'subtitle' => [
                'filter' => FILTER_CALLBACK,
                'options' => 'sanitize_text_field'
            ],
            'excerpt' => [
                'filter' => FILTER_CALLBACK,
                'options' => 'sanitize_textarea_field'
            ],
            'content' => [
                'filter' => FILTER_CALLBACK,
                'options' => 'sanitize_textarea_field'
            ],
            'link' => [
                'filter' => FILTER_CALLBACK,
                'options' => 'sanitize_text_field'
            ],
        ];

        foreach ($sanitized_data as $field => &$args) {
            $args['options'] = apply_filters("backalley/sanitize/press_review/{$field}", $args['options']);
        }

        $sanitized_data = filter_var_array($raw_data, $sanitized_data);

        foreach ($sanitized_data as $field => $new_data) {
            $meta_key = "{$meta_prefix}{$post->post_type}_{$field}";
            $old_data = get_post_meta($post_id, $meta_key, true);

            if ($old_data !== $new_data) {
                update_post_meta($post_id, $meta_key, $new_data);
            }
        }
    }

    /**
     * 
     */
    public static function save_publication_info($post_id, $post, $update, $fieldset = null, $raw_data = null)
    {
        $meta_prefix = BackAlley::$meta_key_prefix;

        $sanitized_data = [
            'publication' => [
                'filter' => FILTER_CALLBACK,
                'options' => 'sanitize_text_field'
            ],
            'date_published' => [
                'filter' => FILTER_CALLBACK,
                'options' => 'sanitize_text_field'
            ],
            'author' => [
                'filter' => FILTER_CALLBACK,
                'options' => 'sanitize_text_field'
            ],
        ];

        foreach ($sanitized_data as $field => &$args) {
            $args['options'] = apply_filters("backalley/sanitize/press_review/{$field}", $args['options']);
        }

        $sanitized_data = filter_var_array($raw_data, $sanitized_data);

        foreach ($sanitized_data as $field => $new_data) {
            $meta_key = "{$meta_prefix}{$post->post_type}_{$field}";
            $old_data = get_post_meta($post_id, $meta_key, true);

            if ($old_data !== $new_data) {
                update_post_meta($post_id, $meta_key, $new_data);
            }
        }
    }
}
