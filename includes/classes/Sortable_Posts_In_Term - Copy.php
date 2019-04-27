<?php

/**
 *
 */
use Timber\Timber;
use Timber\Helper;
use Timber\Twig_Function;

class Sortable_Posts_In_Term
{
    public $taxonomy;

    public $terms;

    public $meta_key_format;

    public function __construct($post_type, $taxonomy, $meta_key_format = null, $fully_loaded = false)
    {
        // parent::__construct();

        $this->set_post_type($post_type);
        $this->set_taxonomy($taxonomy);
        $this->set_terms();

        if ($fully_loaded === true) {
            add_action('admin_menu', $this->add_submenu_page());
            add_action("the_posts", $this->order_objects_query(), null, 2);
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
    public function order_objects_query()
    {
        return function ($posts, $query) {
            if ($query->query_vars['post_type'] !== $this->post_type->name) {
                return $posts;
            }

            $tax_queries = $query->query_vars['tax_query'] ?? null;

            if (!$tax_queries || count($tax_queries) !== 1) {
                return $posts;
            }

            $tax_queries = $tax_queries[0];

            if ($tax_queries['taxonomy'] !== $this->taxonomy->name) {
                return $posts;
            }

            $term = is_numeric($tax_queries['terms']) || is_string($tax_queries['terms']) ? $tax_queries['terms'] : null;
            $field = $tax_queries['field'] ?? null;

            if (!$field || !$term) {
                return $posts;
            }

            if ($field !== 'term_id') {
                $term = get_term_by($field, $term, $this->taxonomy->name, OBJECT)->term_id;
            }
            

            $orderby_apex = "_term{$term}_display_position";
            $orderby_hierarchy = "_term{$term}_hierarchy_display_position";

            $orderby = $query->query_vars['orderby'];

            if ($orderby !== $orderby_apex && $orderby !== $orderby_hierarchy) {
                return $posts;
            }

            $posts = $this->order_objects_array($posts, $orderby_apex, $orderby_hierarchy);

            return $query->query_vars['order'] !== 'DESC' ? $posts : array_reverse($posts);
        };
    }

    /**
     *
     */
    public function usort_objects_array(array $posts, string $order_key = null)
    {
        usort($posts, function ($a, $b) use ($order_key) {
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

        return $posts;
    }

    /**
     *
     */
    public function order_objects_array($posts, $orderby_apex, $orderby_child)
    {
        $apex_posts = [];
        $child_posts = [];

        foreach ($posts as $post) {
            if (!property_exists($post, 'backalley')) {
                $post->backalley = new stdClass;
            }

            if (!property_exists($post->backalley, $orderby_apex)) {
                $post->backalley->{$orderby_apex} = (int) get_post_meta($post->ID, $orderby_apex, true);
            }

            if (empty($post->post_parent)) {
                $apex_posts[] = $post;
            }

            if (!property_exists($post->backalley, $orderby_child) && !empty($post->post_parent)) {
                $post->backalley->{$orderby_child} = (int) get_post_meta($post->ID, $orderby_child, true);

                $child_posts[] = $post;
            }
        }

        $apex_posts = $this->usort_objects_array($apex_posts, $orderby_apex);
        $child_posts = $this->usort_objects_array($child_posts, $orderby_child);

        return array_merge($apex_posts, $child_posts);
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
            $main_page = 'ba_sort_posts';
            add_menu_page(null, null, 'manage_options', $main_page, $this->load_admin_page($this->taxonomy->name));
            remove_menu_page($main_page);

            $parent_slug = htmlspecialchars("edit.php?post_type={$this->post_type->name}");
            $parent_slug = apply_filters("backalley/sortable_taxonomy/{$this->post_type->name}/admin_page/parent_slug", $parent_slug);

            $page_title = 'Arrange Menu Categories';
            $page_title = apply_filters("backalley/sortable_taxonomy/{$this->post_type->name}/admin_page/page_title", $page_title);

            $menu_title = 'Menu Structure';
            $menu_title = apply_filters("backalley/sortable_taxonomy/{$this->post_type->name}/admin_page/menu_title", $menu_title);

            $capability = 'manage_options';
            $capability = apply_filters("backalley/sortable_taxonomy/{$this->post_type->name}/admin_page/capability", $capability);

            $menu_slug = htmlspecialchars("{$main_page}&{$this->taxonomy->name}=0");
            $menu_slug = apply_filters("backalley/sortable_taxonomy/{$this->post_type->name}/admin_page/menu_slug", $menu_slug);

            add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, [$this, 'load_admin_page']);
            remove_submenu_page($parent_slug, $menu_slug);

            add_filter('submenu_file', $this->fix_submenu_file($menu_slug), null, 2);
        };
    }

    /**
     *
     */
    public function fix_submenu_file($menu_slug)
    {
        return function ($submenu_file, $b) use ($menu_slug) {
            $screen = get_current_screen();
                
            if ($screen->base === 'toplevel_page_ba_sort_posts' && $screen->post_type === $this->post_type->name) {
                return htmlspecialchars("ba_sort_terms&taxonomy={$this->taxonomy->name}");
            }

            return $submenu_file;
        };
    }

    /**
     *
     */
    public static function load_admin_page($taxonomy)
    {
        return function () use ($taxonomy) {
            if (!filter_has_var(INPUT_GET, $taxonomy)) {
                echo '<h1>This page cannot be viewed</h1>';
                die;
            }

            $term = filter_has_var(INPUT_GET, $taxonomy) ? sanitize_key($_GET[$taxonomy]) : '';
            $post_type = filter_has_var(INPUT_GET, 'post_type') ? sanitize_key($_GET['post_type']) : '';

            $term = get_term_by('slug', $term, $taxonomy, OBJECT);

            $apex_position_meta_key = "_term{$term->term_id}_display_position";
            $hierarchy_position_meta_key = "_term{$term->term_id}_hierarchy_display_position";
        
            $apex_position_input_name = 'ba_order';
            $hierarchy_position_input_name = 'ba_hierarchy_order';

            // process input data
            if (filter_has_var(INPUT_POST, $apex_position_input_name) || filter_has_var(INPUT_POST, $hierarchy_position_input_name)) {
                $apex_positions = $_REQUEST[$apex_position_input_name] ?? [];
                $hierarchy_positions = $_REQUEST[$hierarchy_position_input_name] ?? [];

                foreach ($apex_positions as $post_id => $position) {
                    update_post_meta($post_id, $apex_position_meta_key, (int) $position);
                }

                foreach ($hierarchy_positions as $post_id => $position) {
                    update_post_meta($post_id, $hierarchy_position_meta_key, (int) $position);
                }
            }
            // end process input data

            $posts = [
                'post_type' => $post_type,
                'order' => 'ASC',
                'orderby' => $apex_position_meta_key,
                'suppress_filters' => false,
                'posts_per_page' => -1,
                'tax_query' => [
                    [
                        'taxonomy' => $taxonomy,
                        'field' => 'slug',
                        'terms' => $term->slug,
                        'include_children' => false,
                        'operator' => 'IN'
                    ]
                ]
            ];

            $posts = get_posts($posts);
            $posts_walker = new Sortable_Objects_Walker;
            $posts_walker->set_object_type('post');

           
            // get objects for constructing ui
            $taxonomy_object = get_taxonomy($taxonomy);
            $post_type_object = get_post_type_object($post_type);

            $posts_walker_args = [
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
            $template_data['objects'] = $posts;
            $template_data['ba_title'] = "Sort {$post_type_object->labels->name} in {$term->name}";
            $template_data['sorted_sortables'] = $posts_walker->walk($posts, 0, $posts_walker_args);

            // render template
            Timber::$locations = BackAlley_Library::$timber_locations;
            Timber::render('admin-page__sortable-objects.twig', $template_data);
        
            // wp_list_sort( $list:array, $orderby:string|array, $order:string, $preserve_keys:boolean );
        };
    }
}
