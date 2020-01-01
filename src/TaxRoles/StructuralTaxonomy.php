<?php

/**
 * @package Backalley-Core
 */

namespace WebTheory\TaxRoles;

use Psr\Http\Message\ServerRequestInterface;
use WebTheory\Leonidas\Fields\WpAdminField;
use WebTheory\Leonidas\Forms\Controllers\TermFieldFormSubmissionManager;
use WebTheory\Leonidas\Term\Field as TermField;
use WebTheory\Saveyour\Fields\Select;
use WebTheory\Saveyour\Managers\FieldDataManagerCallback;

class StructuralTaxonomy
{
    /**
     *
     */
    protected $taxonomy;

    /**
     *
     */
    protected $roles;

    /**
     *
     */
    protected $roles_data;

    /**
     *
     */
    protected $select_options;

    /**
     * role that signifies a term as being of the lowest possible ranking
     */
    protected $baronesque;

    /**
     * role that signifies term is of the highest possible ranking
     */
    protected $sovereign;

    /**
     *
     */
    protected static $post_var = 'backalley_hierarchy_role';

    /**
     *
     */
    protected static $wp_option = 'ba_structural_term_roles';

    /**
     *
     */
    public function __construct($taxonomy, $args = null)
    {
        $this->taxonomy = $taxonomy;
        $this->roles = $args['roles'];
        $this->sovereign = $args['top'];
        $this->baronesque = $args['bottom'];

        $this->set_roles_data();
        $this->add_term_field();
    }

    /**
     *
     */
    public function add_term_field()
    {
        $this->set_select_options();

        $select = (new Select)
            ->setId('backalley--hierarchy-role')
            ->setOptions($this->select_options);

        $manager = new FieldDataManagerCallback([$this, 'get_term_role'], [$this, 'update_term_roles']);

        $controller = (new WpAdminField(static::$post_var, $select, $manager))
            ->addFilter('sanitize_text_field');

        $formManager = (new TermFieldFormSubmissionManager($this->taxonomy->name))
            ->addField($controller)
            ->hook();

        $field = (new TermField($this->taxonomy->name, $controller))
            ->setLabel('Hierarchy Role')
            ->setDescription('Define a purpose for this term within the hierarchy')
            ->hook();

        return $this;
    }

    /**
     *
     */
    protected function set_select_options()
    {
        $options = ['' => 'Select Role'];

        foreach ($this->roles_data as $role) {
            $options[$role['name']] = $role['title'];
        }

        $this->select_options = $options;

        return $this;
    }

    /**
     *
     */
    public function set_roles_data()
    {
        foreach ($this->roles as $new_role => $title) {

            $new_role_row = [
                'name' => sanitize_key($new_role),
                'title' => $title,
                'terms' => [],
                'taxonomy' => $this->taxonomy->name,
                'sovereign' => $new_role === $this->sovereign ? true : false,
                'baronesque' => $new_role === $this->baronesque ? true : false,
            ];

            $this->roles_data[] = $new_role_row;
        }

        return $this;
    }

    /**
     *
     */
    public function update_term_roles(ServerRequestInterface $request, $term_role)
    {
        $term = $request->getAttribute('term', null);
        $roles = get_option($this::$wp_option, []);
        $prev_role = $this::get_term_role($request, $this->taxonomy->name);

        if ($prev_role === $term_role) {
            return false;
        }

        foreach ($roles as &$role) {

            if ($prev_role === $role['name']) {
                $index = array_search($term->term_id, $role['terms']);
                unset($role['terms'][$index]);
            }

            if ($term_role === $role['name']) {
                $role['terms'][] = $term->term_id;
                $found = true;
            }
        }

        if (!isset($found)) {

            foreach ($this->roles_data as $new_row) {
                if ($new_row['name'] === $term_role) {

                    $new_row['terms'][] = $term->term_id;
                    $roles[] = $new_row;
                    break;
                }
            }
        }

        return update_option($this::$wp_option, $roles, false);
    }

    /**
     *
     */
    public function get_term_role(ServerRequestInterface $request)
    {
        $term = $request->getAttribute('term', null);

        if (!$term) {
            return;
        }

        $roles = get_option(Self::$wp_option, []);

        foreach ($roles as $role) {
            if ($role['taxonomy'] !== $term->taxonomy) {
                continue;
            }

            $id = $term->term_id;

            if (in_array((int) $id, $role['terms'])) {

                $name = $role['name'];
                break;
            }
        }

        return $name ?? null;
    }

    /**
     *
     */
    public static function get_role_terms($role, $taxonomy)
    {
        $roles = get_option(Self::$wp_option, []);

        foreach ($roles as $possible_role) {
            if ($possible_role['taxonomy'] !== $taxonomy) {
                continue;
            }

            if ($possible_role['name'] === $role) {
                $terms = $role['terms'];
            }
        }

        return $terms ?? null;
    }
}
