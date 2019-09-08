<?php

namespace Backalley\CleanWp;

/**
 *
 */
class Deblogger
{
    /**
     *
     */
    public static function clear_dashboard()
    {
        add_action('wp_dashboard_setup', function (bool $network = false) {
            // use 'dashboard-network' as the second parameter to remove widgets from a network dashboard.
            $network = $network === false ? '' : '-network';

            remove_meta_box('dashboard_right_now', 'dashboard' . $network, 'normal');   // Right Now
            remove_meta_box('dashboard_recent_comments', 'dashboard' . $network, 'normal'); // Recent Comments
            remove_meta_box('dashboard_incoming_links', 'dashboard' . $network, 'normal');  // Incoming Links
            remove_meta_box('dashboard_plugins', 'dashboard . $network', 'normal');   // Plugins
            remove_meta_box('dashboard_quick_press', 'dashboard' . $network, 'side');  // Quick Press
            remove_meta_box('dashboard_recent_drafts', 'dashboard' . $network, 'side');  // Recent Drafts
            remove_meta_box('dashboard_primary', 'dashboard' . $network, 'side');   // WordPress blog
            remove_meta_box('dashboard_secondary', 'dashboard' . $network, 'side');   // Other WordPress News
            remove_meta_box('dashboard_activity', 'dashboard' . $network, 'normal');   // Activity
        });
    }

    /**
     * sets default post type "post" _builtin value to false so that it can be unregistered
     */
    public static function unregister_builtin_post_types($post_type = 'post')
    {
        global $wp_post_types;
        $wp_post_types[$post_type]->_builtin = false;

        unregister_post_type($post_type);
    }
}
