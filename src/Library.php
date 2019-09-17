<?php

namespace Backalley;

use Backalley\SortableObjects\SortablePostsInTerm;
use Backalley\SortableObjects\SortableTaxonomy;
use Backalley\SortableObjects\SortedFilteredClonedQuery;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

class Library extends \BackalleyLibraryBase
{
    /**
     *
     */
    public static $options;

    /**
     * @var Environment
     */
    protected static $twigInstance;

    /**
     *
     */
    public static function init(array $options = [])
    {
        static::$options = $options;

        static::load();
        static::aliasClasses();
        static::initTwig();
        static::hook();
    }

    /**
     *
     */
    protected static function hook()
    {
        add_action('admin_enqueue_scripts', [static::class, 'enqueue']);
        add_action('admin_menu', [static::class, 'registerPages']);
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
        wp_enqueue_script('backalley-starter-script--sort-objects', static::$admin_url . '/assets/js/backalley-sortable-objects.js', null, time(), true);

        # backalley styles
        wp_enqueue_style('backalley-starter-styles--sort-objects', static::$admin_url . '/assets/css/backalley-sortable-objects.css', null, time());
    }

    /**
     *
     */
    protected static function initTwig()
    {
        $options = [
            'autoescape' => false,
        ];

        $loader = new FilesystemLoader(static::$admin_templates);

        $twig = new Environment($loader, $options);

        static::configTwig($twig);

        static::$twigInstance = $twig;
    }

    /**
     *
     */
    public static function renderTemplate($template, $context)
    {
        return static::$twigInstance->render("{$template}.twig", $context);
    }

    /**
     *
     */
    protected static function configTwig($twig)
    {
        static::addTwigFilters($twig);
        static::addTwigFunctions($twig);

        return $twig;
    }

    /**
     *
     */
    protected static function addTwigFilters($twig)
    {
        $filters = [];

        foreach ($filters as $filter => $function) {
            $twig->addFilter(new TwigFilter($filter, $function));
        }
    }

    /**
     *
     */
    protected static function addTwigFunctions($twig)
    {
        $functions = [
            'submit_button' => 'submit_button',
            'settings_fields' => 'settings_fields',
            'do_settings_sections' => 'do_settings_sections',
            'settings_errors' => 'settings_errors',
        ];

        foreach ($functions as $alias => $function) {
            $twig->addFunction(new TwigFunction($alias, $function));
        }
    }

    /**
     *
     */
    protected static function aliasClasses()
    {
        $aliases = [
            "SFC_Query" => SortedFilteredClonedQuery::class,
        ];

        foreach ($aliases as $alias => $class) {
            class_alias($class, $alias);
        }
    }

    /**
     *
     */
    public static function registerPages()
    {
        SortableTaxonomy::register_admin_page();
        SortablePostsInTerm::register_admin_page();
    }
}
