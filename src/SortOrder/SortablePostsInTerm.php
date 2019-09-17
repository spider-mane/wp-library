<?php

/**
 * @package Backalley-Starter
 */

namespace Backalley\SortOrder;

use Backalley\Library;
use Backalley\SortOrder\SortableObjectsBase;
use Backalley\SortOrder\SortableObjectsWalker;
use Backalley\SortOrder\SortedFilteredClonedQuery;

class SortablePostsInTerm extends SortableObjectsBase
{
    public $terms = [];
    public $taxonomy;
    public $admin_uri;
    public $parent_slug;
    public $submenu_slug;
    public $submenu_args;

    public static $current_term;
    public static $taxonomies = [];
    public static $admin_page_slug;

    public function __construct(string $post_type, string $taxonomy, array $ui_args)
    {
        // set properties from $ui_args
        $this->submenu_args = $ui_args['submenu_page'] ?? null;
        // $this->action_args = $ui_args['term_row_action'] ?? null;

        // setter methods
        $this->set_post_type($post_type);
        $this->set_taxonomy($taxonomy);
        $this->set_terms();

        // static properties
        $this::$taxonomies[] = $this->taxonomy->name;

        //register callback to sort queries according to order values
        add_action("the_posts", [$this, 'order_objects_query'], null, 2);

        // add_action("admin_notices", $this->admin_notices());

        if (isset($ui_args) && $ui_args !== false) {

            // create admin subpage
            if ((!empty($this->submenu_args) && $this->submenu_args !== false)) {
                add_action('admin_menu', [$this, 'add_submenu_page']);
                add_filter('admin_title', [$this, 'fix_subpage_title'], null, 2);
                add_filter('submenu_file', [$this, 'fix_submenu_file'], null, 2);
            }

            // add row action to edit-tags.php
            if (isset($ui_args['term_row_action']) && $ui_args['term_row_action'] !== false) {
                add_filter("{$this->taxonomy->name}_row_actions", [$this, 'add_row_action'], null, 2);
            }
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
    public function order_objects_query($posts, $query)
    {
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

        $posts = $this::order_objects_array($posts, 'post', $orderby_apex, $orderby_hierarchy);

        return $query->query_vars['order'] !== 'DESC' ? $posts : array_reverse($posts);
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

        };
    }

    /**
     *
     */
    public function add_submenu_page()
    {
        $this->parent_slug = $this->submenu_args['parent_slug'] ?? "edit.php?post_type={$this->post_type->name}";
        $this->submenu_slug = "{$this::$admin_page_slug}&post_type={$this->post_type->name}&{$this->taxonomy->name}";
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
    public function fix_submenu_file($submenu_file, $menu_file)
    {
        $screen = get_current_screen();

        if ($screen->base === "toplevel_page_{$this::$admin_page_slug}" && $screen->post_type === $this->post_type->name) {
            return htmlspecialchars($this->submenu_args['submenu_file'] ?? $this->submenu_slug);
        }
        // "ba_sort_terms&taxonomy={$this->taxonomy->name}"; // find a clean way to make this a simple option
        return $submenu_file;
    }

    /**
     *
     */
    public function fix_subpage_title($admin_title, $title)
    {
        $screen = get_current_screen();

        if ($screen->base === "toplevel_page_{$this::$admin_page_slug}" && $screen->post_type === $this->post_type->name) {
            $term = filter_has_var(INPUT_GET, $this->taxonomy->name) ? sanitize_key($_GET[$this->taxonomy->name]) : null;

            if (isset($term) && term_exists($term, $this->taxonomy->name)) {
                $term = get_term_by('slug', $term, $this->taxonomy->name, OBJECT);
                $page_title = "Sort {$this->post_type->label} in {$term->name}";
            }

            $page_title = $this->submenu_args['page_title'] ?? $page_title ?? "Sort {$this->post_type->label} in Terms";

            return "{$page_title}{$admin_title}";
        }

        return $admin_title;
    }

    /**
     *
     */
    public function add_row_action($actions, $term)
    {
        $href = "{$this->admin_uri}={$term->slug}";
        $aria_label = "Sort {$this->post_type->label} in &#8220;{$term->name}&#8221;";
        $content = "Sort {$this->post_type->label}";

        $actions["sort_{$this->post_type->name}"] = "<a href=\"$href\" aria-label=\"$aria_label\">$content</a>";

        return $actions;
    }

    /**
     *
     */
    public static function register_admin_page($menu_slug = null)
    {
        Self::$admin_page_slug = $menu_slug ?? 'ba_sort_posts';

        add_menu_page(null, null, 'manage_options', Self::$admin_page_slug, [__class__, 'load_admin_page']);
        remove_menu_page(Self::$admin_page_slug);
    }

    /**
     *
     */
    public static function load_admin_page()
    {
        foreach (Self::$taxonomies as $taxonomy) {
            if (filter_has_var(INPUT_GET, $taxonomy)) {
                break;
            }
        }

        if (!filter_has_var(INPUT_GET, $taxonomy) || !term_exists($_GET[$taxonomy], $taxonomy)) {
            echo '<h1>This page cannot be viewed</h1>';
            return;
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
        $posts_walker = new SortableObjectsWalker;
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

        $template_data['title'] = "Sort {$post_type_object->labels->name} in {$term->name}";
        $template_data['sorted_sortables'] = $posts_walker->walk($posts, 0, $posts_walker_args);

        echo Library::renderTemplate('admin-page__sortable-objects', $template_data);
    }

    /**
     * Creates a clone of a query object and filters and sorts the posts array by the meta key that. Basically a
     * pointless, but semantically accurate wrapper for SFC_Query.
     *
     * @return object returns a clone of either the default query or a supplied custom query with modified posts
     */
    public static function get_sorted_filtered_cloned_query($term_id, $taxonomy, $query = null)
    {
        return new SortedFilteredClonedQuery($term_id, $taxonomy, $query);
    }
}
