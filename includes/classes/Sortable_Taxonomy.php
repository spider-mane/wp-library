<?php

/**
 * @package Backalley-Starter
 */

use Backalley\Backalley;
use Timber\Timber;
use Timber\Helper;
use Timber\Twig_Function;
use function DeepCopy\deep_copy;


class Sortable_Taxonomy extends Sortable_Objects_Base
{
    public $taxonomy;
    public $submenu_args;
    public $terms;
    public $admin_uri;
    public $parent_slug;
    public $submenu_slug;

    static $admin_page_slug;

    public function __construct($taxonomy, $post_type, $ui_args)
    {
        // set properties from $ui_args
        $this->submenu_args = $ui_args['submenu_page'] ?? null;
        // $this->action_args = $ui_args['term_row_action'] ?? null;

        // setter methods
        $this->set_taxonomy($taxonomy);
        $this->set_post_type($post_type);
        $this->set_terms();

        // static properties


        //register callback to sort queries according to order values
        add_action("get_terms", [$this, 'order_terms_query'], null, 4);
        // add_action("the_posts", [$this, 'order_objects_query'], null, 2);
        
        // add_action("admin_notices", $this->admin_notices());

        if (isset($ui_args) && $ui_args !== false) {

            // create admin subpage
            if ((!empty($this->submenu_args) && $this->submenu_args !== false)) {
                add_action('admin_menu', [$this, 'add_submenu_page']);
                add_filter('admin_title', [$this, 'fix_subpage_title'], null, 2);
                add_filter('submenu_file', [$this, 'fix_submenu_file'], null, 2);
            }

            // add row action to edit-tags.php
            // if (isset($ui_args['term_row_action']) && $ui_args['term_row_action'] !== false) {
            //     add_filter("{$this->taxonomy->name}_row_actions", [$this, 'add_row_action'], null, 2);
            // }
        }
    }

    /**
     *
     */
    public function set_taxonomy($taxonomy)
    {
        $this->taxonomy = get_taxonomy($taxonomy);
    }

    /**
     *
     */
    public function set_post_type($post_type)
    {
        $this->post_type = get_post_type_object($post_type);
    }

    /**
     *
     */
    public function set_terms()
    {
        $terms = [
            'taxonomy' => $this->taxonomy->name,
            'hide_empty' => false
        ];

        $terms = get_terms($terms);

        foreach ($terms as $term) {
            $this->terms[$term->slug] = $term;
        }

        ksort($this->terms);
    }

    /**
     *
     */
    public function order_terms_query($terms, $taxonomy, $args, $query)
    {
        $orderby_apex = "_{$this->post_type->name}_display_position";
        $orderby_hierarchy = "_{$this->post_type->name}_hierarchy_display_position";

        $orderby = $query->query_vars['orderby'];

        if ($orderby !== $orderby_apex && $orderby !== $orderby_hierarchy) {
            return $terms;
        }

        $terms = $this::order_objects_array($terms, 'term', $orderby_apex, $orderby_hierarchy);

        return $query->query_vars['order'] !== 'DESC' ? $terms : array_reverse($terms);
    }

    /**
     *
     */
    public function admin_notices($a)
    {
        $message = null;

        if (get_transient('display_orders_bulk_updated')) {
            $message = "display positions updated";
            $transient = "display_orders_bulk_updated";
        } elseif (get_transient("single_display_order_updated")) {
            $message = "display order successfully updated";
            $transient = "single_display_order_updated";
        }

        if (isset($transient)) {
            delete_transient($transient); ?>

            <div id="message" class="notice notice-success is-dismissible">
                <p><?= $message ?></p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>

            <?php

        }
    }

    /**
     *
     */
    public function add_submenu_page($context)
    {
        $this->parent_slug = $this->submenu_args['parent_slug'] ?? "edit.php?post_type={$this->post_type->name}";
        $this->submenu_slug = "{$this::$admin_page_slug}&taxonomy={$this->taxonomy->name}";
        $this->admin_uri = "{$this->parent_slug}&page={$this->submenu_slug}";

        $parent_slug = htmlspecialchars($this->parent_slug);
        $menu_title = $this->submenu_args['menu_title'] ?? "";
        $capability = $this->submenu_args['capability'] ?? ""; // needs workaround
        $menu_slug = htmlspecialchars($this->submenu_slug);

        add_submenu_page($parent_slug, null, $menu_title, $capability, $menu_slug, [$this, 'load_admin_page']);

        if (isset($this->submenu_args['display']) && $this->submenu_args['display'] === false) {
            remove_submenu_page($parent_slug, $menu_slug);
        }
    }

    /**
     *
     */
    public function fix_submenu_file($submenu_file, $parent_file)
    {
        $screen = get_current_screen();

        if ($screen->base === "toplevel_page_{$this::$admin_page_slug}" && $screen->post_type === $this->post_type->name && $screen->taxonomy === $this->taxonomy->name) {
            return htmlspecialchars($this->submenu_args['submenu_file'] ?? $this->submenu_slug);
        }

        return $submenu_file;
    }

    /**
     * 
     */
    public function fix_subpage_title($admin_title, $title)
    {
        $screen = get_current_screen();

        if ($screen->base === "toplevel_page_{$this::$admin_page_slug}" && $screen->post_type === $this->post_type->name && $screen->taxonomy === $this->taxonomy->name) {

            $page_title = $this->submenu_args['page_title'] ?? "Sort {$this->taxonomy->name} for {$this->post_type->label}";

            return $page_title . $admin_title;
        }

        return $admin_title;
    }

    /**
     * 
     */
    public static function register_admin_page($menu_slug = null)
    {
        Self::$admin_page_slug = 'ba_sort_terms';

        add_menu_page(null, null, 'manage_options', Self::$admin_page_slug, [__class__, 'load_admin_page']);
        remove_menu_page(Self::$admin_page_slug);
    }

    /**
     *
     */
    public static function load_admin_page()
    {
        $taxonomy = filter_has_var(INPUT_GET, 'taxonomy') ? sanitize_key($_GET['taxonomy']) : '';
        $post_type = filter_has_var(INPUT_GET, 'post_type') ? sanitize_key($_GET['post_type']) : '';

        $apex_position_meta_key = "_{$post_type}_display_position";
        $hierarchy_position_meta_key = "_{$post_type}_hierarchy_display_position";

        $apex_position_input_name = 'ba_order';
        $hierarchy_position_input_name = 'ba_hierarchy_order';

        // process input data
        if (filter_has_var(INPUT_POST, $apex_position_input_name) || filter_has_var(INPUT_POST, $hierarchy_position_input_name)) {
            $apex_positions = $_REQUEST[$apex_position_input_name] ?? [];
            $hierarchy_positions = $_REQUEST[$hierarchy_position_input_name] ?? [];

            foreach ($apex_positions as $term_id => $position) {
                update_term_meta($term_id, $apex_position_meta_key, (int)$position);
            }

            foreach ($hierarchy_positions as $term_id => $position) {
                update_term_meta($term_id, $hierarchy_position_meta_key, (int)$position);
            }
        }
        // end process input data

        $terms = [
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'orderby' => $apex_position_meta_key
        ];

        $terms = get_terms($terms);
        $terms_walker = new Sortable_Objects_Walker;
        $terms_walker->set_object_type('term');


        $taxonomy_object = get_taxonomy($taxonomy);
        $post_type_object = get_post_type_object($post_type);

        $terms_walker_args = [
            'apex_meta_key' => $apex_position_meta_key,
            'hierarchy_meta_key' => $hierarchy_position_meta_key,
            'ul_classes' => 'hierarchy sortable sortable--group',
            'li_classes' => 'sortable--item-container',
            'object_div_classes' => '',
            'common_input_classes' => 'order-input small 0hide-if-js',
            'apex_input_classes' => 'order-input--apex',
            'hierarchy_input_classes' => 'order-input--hierarchy',
        ];

        // create array of values to pass template
        $template_data['objects'] = $terms;
        $template_data['ba_title'] = "Sort {$taxonomy_object->label} for {$post_type_object->labels->name}";
        $template_data['sorted_sortables'] = $terms_walker->walk($terms, 0, $terms_walker_args);

        // render template
        Timber::$locations = BackAlley_Library::$timber_locations;
        Timber::render('admin-page__sortable-objects.twig', $template_data);
    }

    /**
     *
     */
    public static function update_meta_keys($old_post_type_name, $new_post_type_name)
    {
        $old_meta_key = "_{$old_post_type_name}_display_position";
    }
}
