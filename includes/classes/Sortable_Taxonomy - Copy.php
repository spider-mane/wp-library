<?php

/**
 *
 */
use Timber\Timber;
use Timber\Helper;
use Timber\Twig_Function;

class Sortable_Taxonomy
{
    public $taxonomy;

    public $terms;

    public $meta_key_format;

    public function __construct($taxonomy, $post_type, $meta_key_format = null, $fully_loaded = false)
    {
        // parent::__construct();

        $this->set_taxonomy($taxonomy);
        $this->set_post_type($post_type);
        $this->set_terms();

        if ($fully_loaded === true) {
            add_action('admin_menu', $this->add_submenu_page());
            add_action("get_terms", $this->order_terms_query(), null, 4);
            // add_action('admin_enqueue_scripts', [$this, 'enqueue_stuffs']);
            // add_action("enqueue_admin_styles")
            // add_action('load-edit.php', $this->fast_update_single_post($this->taxonomy));
            // add_action("admin_notices", $this->admin_notices());
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
    public function order_terms_query()
    {
        return function ($terms, $taxonomy, $args, $query) {
            $orderby_apex = "_{$this->post_type->name}_display_position";
            $orderby_child = "_{$this->post_type->name}_hierarchy_display_position";

            $orderby = $query->query_vars['orderby'];

            if ($orderby !== $orderby_apex && $orderby !== $orderby_child) {
                return $terms;
            }

            $terms = $this->order_terms_array($terms, $orderby_apex, $orderby_child);

            return $query->query_vars['order'] !== 'DESC' ? $terms : array_reverse($terms);
        };
    }

    /**
     *
     */
    public function sort_terms_array(array $terms, string $order_key = null)
    {
        usort($terms, function ($a, $b) use ($order_key) {
            $a = (int) $a->backalley->{$order_key} >= 0 ? $a->backalley->{$order_key} : 0;
            $b = (int) $b->backalley->{$order_key} >= 0 ? $b->backalley->{$order_key} : 0;

            if ($a === $b) {
                return 0;
            }
    
            if ($a < $b && $a === 0) {
                return  1;
            }
    
            if ($a > $b && $b === 0) {
                return -1;
            }
    
            return $a > $b ? 1 : -1;
        });

        return $terms;
    }

    /**
     *
     */
    public function order_terms_array($terms, $orderby_apex, $orderby_child)
    {
        $apex_terms = [];
        $child_terms = [];

        foreach ($terms as $term) {
            if (!property_exists($term, 'backalley')) {
                $term->backalley = new stdClass;
            }

            if (!property_exists($term->backalley, $orderby_apex)) {
                $term->backalley->{$orderby_apex} = (int) get_term_meta($term->term_id, $orderby_apex, true);
            }

            if (empty($term->parent)) {
                $apex_terms[] = $term;
            }

            if (!property_exists($term->backalley, $orderby_child) && !empty($term->parent)) {
                $term->backalley->{$orderby_child} = (int) get_term_meta($term->term_id, $orderby_child, true);

                $child_terms[] = $term;
            }
        }

        $apex_terms = $this->sort_terms_array($apex_terms, $orderby_apex);
        $child_terms = $this->sort_terms_array($child_terms, $orderby_child);

        return array_merge($apex_terms, $child_terms);
    }

    /**
     *
     */
    public function admin_notices()
    {
        return function ($a) {
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
        };
    }

    /**
     *
     */
    public function add_submenu_page()
    {
        return function ($context) {
            $main_page = 'ba_sort_terms';

            add_menu_page(null, null, 'manage_options', $main_page, [$this, 'load_admin_page']);
            remove_menu_page($main_page);

            $parent_slug = htmlspecialchars("edit.php?post_type={$this->post_type->name}");
            $parent_slug = apply_filters("backalley/sortable_taxonomy/{$this->post_type->name}/admin_page/parent_slug", $parent_slug);

            $page_title = 'Arrange Menu Categories';
            $page_title = apply_filters("backalley/sortable_taxonomy/{$this->post_type->name}/admin_page/page_title", $page_title);

            $menu_title = 'Menu Structure';
            $menu_title = apply_filters("backalley/sortable_taxonomy/{$this->post_type->name}/admin_page/menu_title{$menu_title}", $menu_title);

            $capability = 'manage_options';
            $capability = apply_filters("backalley/sortable_taxonomy/{$this->post_type->name}/admin_page/capability", $capability);

            $menu_slug = htmlspecialchars("{$main_page}&taxonomy={$this->taxonomy->name}");
            $menu_slug = apply_filters("backalley/sortable_taxonomy/{$this->post_type->name}/admin_page/menu_slug", $menu_slug);

            add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, [$this, 'load_admin_page']);

            add_filter('submenu_file', $this->fix_submenu_file($main_page, $menu_slug), null, 2);
        };
    }

    /**
     *
     */
    public function fix_submenu_file($main_page, $menu_slug)
    {
        return function ($submenu_file, $parent_file) use ($main_page, $menu_slug) {
            $screen = get_current_screen();

            if ($screen->base === "toplevel_page_{$main_page}" && $screen->post_type === $this->post_type->name && $screen->taxonomy === $this->taxonomy->name) {
                return $menu_slug;
            }

            return $submenu_file;
        };
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
            $apex_positions = $_REQUEST[$apex_position_input_name];
            $hierarchy_positions = $_REQUEST[$hierarchy_position_input_name];

            foreach ($apex_positions as $term_id => $position) {
                update_term_meta($term_id, $apex_position_meta_key, (int) $position);
            }

            foreach ($hierarchy_positions as $term_id => $position) {
                update_term_meta($term_id, $hierarchy_position_meta_key, (int) $position);
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
