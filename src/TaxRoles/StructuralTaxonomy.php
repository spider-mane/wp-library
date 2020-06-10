<?php

namespace WebTheory\TaxRoles;

use Psr\Http\Message\ServerRequestInterface;
use WebTheory\Leonidas\Auth\Nonce;
use WebTheory\Leonidas\Fields\WpAdminField;
use WebTheory\Leonidas\Forms\Controllers\TermFieldFormSubmissionManager;
use WebTheory\Leonidas\Term\Field as TermField;
use WebTheory\Leonidas\Term\FieldLoader as TermFieldLoader;
use WebTheory\Saveyour\Fields\Select;
use WebTheory\Saveyour\Managers\FieldDataManagerCallback;

class StructuralTaxonomy
{
    /**
     * @var string
     */
    protected $taxonomy;

    /**
     *
     */
    protected $roles;

    /**
     *
     */
    protected $rolesData;

    /**
     * role that signifies a term as being of the lowest possible ranking
     */
    protected $baronesque;

    /**
     * role that signifies term is of the highest possible ranking
     */
    protected $sovereign;

    /**
     * @var Nonce
     */
    protected static $nonce;

    /**
     * @var string[]
     */
    protected static $taxonomies = [];

    /**
     *
     */
    protected const REQUEST_VAR = 'wts_hierarchy_role';

    /**
     *
     */
    protected const WP_OPTION = 'wts_structural_term_roles';

    /**
     *
     */
    public function __construct(string $taxonomy, array $roles, string $sovereign, string $baronesque)
    {
        static::init();

        $this->taxonomy = $taxonomy;
        $this->roles = $roles;
        $this->sovereign = $sovereign;
        $this->baronesque = $baronesque;

        $this->setRolesData();
        $this->addTermField();
        $this->addTaxonomy();
    }

    /**
     *
     */
    protected function addTaxonomy()
    {
        if (!in_array($this->taxonomy, static::$taxonomies)) {
            static::$taxonomies[] = $this->taxonomy;
            $this->outputNonce();
        }
    }

    /**
     *
     */
    protected function outputNonce()
    {
        (new TermFieldLoader($this->taxonomy))
            ->setNonce(static::$nonce)
            ->hook();
    }

    /**
     *
     */
    public function addTermField()
    {
        $select = (new Select)
            ->setId('backalley--hierarchy-role')
            ->setOptions($this->getSelectOptions());

        $dataManager = new FieldDataManagerCallback([$this, 'getTermRole'], [$this, 'updateTermRoles']);

        $controller = (new WpAdminField(static::REQUEST_VAR, $select, $dataManager))
            ->addFilter('sanitize_text_field');

        $field = (new TermField($controller))
            ->setLabel('Hierarchy Role')
            ->setDescription('Define a purpose for this term within the hierarchy');

        (new TermFieldFormSubmissionManager($this->taxonomy))
            ->setNonce(static::$nonce)
            ->addField($controller)
            ->hook();

        (new TermFieldLoader($this->taxonomy, $field))
            ->hook();

        return $this;
    }

    /**
     *
     */
    protected function getSelectOptions()
    {
        $options = ['' => 'Select Role'];

        foreach ($this->rolesData as $role) {
            $options[$role['name']] = $role['title'];
        }

        return $options;
    }

    /**
     *
     */
    public function setRolesData()
    {
        foreach ($this->roles as $role => $title) {

            $roleRow = [
                'name' => sanitize_key($role),
                'title' => $title,
                'terms' => [],
                'taxonomy' => $this->taxonomy,
                'sovereign' => $role === $this->sovereign ? true : false,
                'baronesque' => $role === $this->baronesque ? true : false,
            ];

            $this->rolesData[] = $roleRow;
        }

        return $this;
    }

    /**
     *
     */
    public function updateTermRoles(ServerRequestInterface $request, $term_role)
    {
        $term = $request->getAttribute('term', null);
        $roles = get_option(static::WP_OPTION, []);
        $prevRole = $this->getTermRole($request, $this->taxonomy);

        if ($prevRole === $term_role) {
            return false;
        }

        foreach ($roles as &$role) {

            if ($prevRole === $role['name']) {
                $index = array_search($term->term_id, $role['terms']);
                unset($role['terms'][$index]);
            }

            if ($term_role === $role['name']) {
                $role['terms'][] = $term->term_id;
                $found = true;
            }
        }

        if (!isset($found)) {

            foreach ($this->rolesData as $new_row) {
                if ($new_row['name'] === $term_role) {

                    $new_row['terms'][] = $term->term_id;
                    $roles[] = $new_row;
                    break;
                }
            }
        }

        return update_option(static::WP_OPTION, $roles, false);
    }

    /**
     *
     */
    public function getTermRole(ServerRequestInterface $request)
    {
        $term = $request->getAttribute('term', null);

        if (!$term) {
            return;
        }

        $roles = get_option(static::WP_OPTION, []);

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
    protected static function init()
    {
        static $initiated;

        if (!isset($initiated)) {
            static::$nonce = new Nonce(
                'wts-structural-taxonomy-nonce',
                'wts-update-structural-taxonomy-value'
            );

            $initiated = true;
        }
    }

    /**
     *
     */
    public static function getRoleTerms($role, $taxonomy)
    {
        $roles = get_option(static::WP_OPTION, []);

        foreach ($roles as $possibleRole) {
            if ($possibleRole['taxonomy'] !== $taxonomy) {
                continue;
            }

            if ($possibleRole['name'] === $role) {
                $terms = $role['terms'];
            }
        }

        return $terms ?? null;
    }
}
