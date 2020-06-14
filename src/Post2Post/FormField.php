<?php

namespace WebTheory\Post2Post;

use Respect\Validation\Validator;
use WebTheory\Leonidas\Fields\WpAdminField;
use WebTheory\Saveyour\Fields\Checklist;

class FormField extends WpAdminField
{
    use HasContextArgumentTrait;

    /**
     * @var Relationship
     */
    protected $relationship;

    /**
     * @var string
     */
    protected $context;

    /**
     * @var array
     */
    protected $options = [
        'id' => 'wts--checklist'
    ];

    /**
     *
     */
    public function __construct(string $requestVar, string $context, Relationship $relationship, ?array $options = null)
    {
        $this->context = $this->throwExceptionIfInvalidContext($context, $relationship);
        $this->relationship = $relationship;
        $options && $this->options = $options;

        parent::__construct(
            $requestVar,
            $this->createFormField(),
            $this->createDataManager(),
            $this->createDataTransformer()
        );

        $this->setNonConstructorProperties();
    }

    /**
     *
     */
    protected function setNonConstructorProperties()
    {
        $this->addRule('int', Validator::intVal(), 'A selection is invalid.');
        $this->setFilters('sanitize_text_field');
    }

    /**
     *
     */
    protected function createFormField()
    {
        $query = $this->relationship->getRelatedPostTypePostsQuery($this->context);
        $items = new PostChecklistItems($query);

        return (new Checklist)
            ->setItems($items->getSelection())
            ->setToggleControl('0')
            ->setId($this->options['id'])
            ->addClass('thing');
    }

    /**
     *
     */
    public function createDataManager()
    {
        return new TermRelatedPostsManager($this->relationship);
    }

    /**
     *
     */
    protected function createDataTransformer()
    {
        return new RelationshipToChecklistDataTransformer();
    }
}
