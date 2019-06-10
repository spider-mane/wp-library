<?php

namespace Backalley;


/**
 * @package backalley-starter
 * 
 * Change ui attributes of the "Post" post type to establish it as the blog feature of
 * site rather than primary purpose as suggested by defaults
 */
class CustomBlog
{
    /**
     * 
     */
    public static function create_from_post($description, $icon)
    {
        global $wp_post_types;
        $wp_post = $wp_post_types['post'];

        // $wp_post->name = "blog";
        $wp_post->label = "Blog";

        // convert "post" in all labels to "blog post"
        $labels = $wp_post->labels;

        foreach ($labels as $label => $value) {
            $upper = "/Post/";
            $lower = "/post/";

            if (preg_match($upper, $value)) {
                $labels->$label = preg_replace($upper, "Blog Post", $value);
            } elseif (preg_match($lower, $value)) {
                $labels->$label = preg_replace($lower, "blog post", $value);
            }
        }

        // $labels->name = "Blog";
        $labels->menu_name = "Blog";

        $wp_post->description = $description ?? 'blog';
        $wp_post->menu_position = 9;
        $wp_post->menu_icon = $icon ?? 'dashicons-welcome-write-blog';

        //setting default post type "post" _builtin value to false allows it to be unregistered
        // $wp_post->_builtin = false;

        $supports = [
            'trackbacks',
            'custom-fields',
            'comments',
        ];

        foreach ($supports as $support) {
            remove_post_type_support('post', $support);
        }
    }
}