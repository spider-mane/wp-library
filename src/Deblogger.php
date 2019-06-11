<?php

namespace Backalley;

/**
 * 
 */
class Deblogger
{
    /**
     * 
     */
    function clear_dashboard()
    {
        add_action('wp_dashboard_setup', function () {
            // use 'dashboard-network' as the second parameter to remove widgets from a network dashboard.
            remove_meta_box('dashboard_right_now', 'dashboard', 'normal');   // Right Now
            remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal'); // Recent Comments
            remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');  // Incoming Links
            remove_meta_box('dashboard_plugins', 'dashboard', 'normal');   // Plugins
            remove_meta_box('dashboard_quick_press', 'dashboard', 'side');  // Quick Press
            remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');  // Recent Drafts
            remove_meta_box('dashboard_primary', 'dashboard', 'side');   // WordPress blog
            remove_meta_box('dashboard_secondary', 'dashboard', 'side');   // Other WordPress News
            remove_meta_box('dashboard_activity', 'dashboard', 'normal');   // Activity
        });
    }
}