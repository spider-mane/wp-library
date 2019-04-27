<?php

/**
 * @package Backalley Starter Library
 */


final class BackAlley_Library
{
    public static $args;
    public static $path;
    public static $url;
    public static $templates;
    public static $timber_locations;

    final public static function init($args)
    {
        Self::$args = $args;

        self::$path = __DIR__;
        self::$url = plugin_dir_url(__FILE__);
        self::$templates = self::$path . DIRECTORY_SEPARATOR . 'views';

        self::$timber_locations = [
            Self::$templates,
            Self::$templates . '/macros',
        ];

        Self::super_set();

        add_action('admin_menu', function () {
            Sortable_Taxonomy::register_admin_page();
            Sortable_Posts_In_Term::register_admin_page();
        });
    }

    /**
     * 
     */
    public static function super_set($super_set = null)
    {
        foreach (Self::$args['super_set'] ?? [] as $conceptual_post_type => $super_set) {

            foreach ($super_set as $super_set => $set_args) {
                $set_args = is_array($set_args) ? $set_args : [$set_args];
                $super_set = "super_set_{$super_set}";

                $conceptual_post_type::$super_set(...$set_args);
            }
        }
    }
}



#Composer Autoload
if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require_once dirname(__FILE__) . '/vendor/autoload.php';
}

#Dependencies

#Controllers
require_once 'includes/enqueue.php';
require_once 'includes/timber-rules.php';

#Classes
require_once "includes/classes/Somewhat_Relatable_Post_Type.php";
require_once "includes/classes/Sortable_Objects_Base.php";
require_once "includes/classes/Sortable_Posts_In_Term.php";
require_once "includes/classes/Sortable_Taxonomy.php";
require_once "includes/classes/Sortable_Objects_Walker.php";
require_once "includes/classes/Sorted_Filtered_Query.php";
require_once 'includes/classes/Backalley_Location.php';
require_once 'includes/classes/Backalley_Menu_Item.php';
require_once 'includes/classes/Backalley_Press_Review.php';
require_once 'includes/classes/Backalley_Restaurant_Location.php';
