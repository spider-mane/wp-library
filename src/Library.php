<?php

/**
 * @package Backalley-Core
 */

namespace Backalley;

use Twig_Function;
use Twig_SimpleFilter;
use Twig_Extension_StringLoader;

use function DeepCopy\deep_copy;

class Library extends \BackalleyLibraryBase
{
    public static $args;

    /**
     * 
     */
    public static function init(array $args = [])
    {
        Self::load();

        Self::$args = $args;

        add_action('admin_enqueue_scripts', [__class__, 'enqueue']);
        add_filter('timber/twig', [__class__, 'config_twig']);

        Self::alias_classes();
        Self::super_set();

        add_action('admin_menu', function () {
            SortableTaxonomy::register_admin_page();
            SortablePostsInTerm::register_admin_page();
        });
    }

    /**
     * 
     */
    public static function enqueue()
    {
        # wp included libraries
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-sortable');

        # backalley scripts
        wp_enqueue_script('backalley-starter-script--sort-objects', Self::$admin_url . '/assets/js/backalley-sortable-objects.js', null, time(), true);
    
        # backalley styles
        wp_enqueue_style('backalley-starter-styles--sort-objects', Self::$admin_url . '/assets/css/backalley-sortable-objects.css', null, time());
    }

    /**
     * 
     */
    public static function config_twig($twig)
    {
        $twig->addExtension(new Twig_Extension_StringLoader());
        $twig->addFilter(new Twig_SimpleFilter('subjectify_objects', 'backalley_subjectify_wp_objects'));

        $twig->addFilter(new Twig_SimpleFilter('clone_original', function ($original) {
            return deep_copy($original);
        }));

        $twig->addFilter(new Twig_SimpleFilter('sort_terms_hierarchicaly', function ($var) {
        // $filtered = [];
            sort_terms_hierarchicaly($var, $filtered);
            return $filtered;
        }));

        $twig->addFunction(new Twig_Function('wp_submit_button', 'submit_button'));

        return $twig;
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

    /**
     * 
     */
    public static function alias_classes()
    {
        $aliases = [
            "Backalley\\SortedFilteredClonedQuery" => "SFC_Query"
        ];

        foreach ($aliases as $class => $alias) {
            class_alias($class, $alias);
        }
    }
}