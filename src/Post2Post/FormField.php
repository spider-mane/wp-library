<?php

namespace WebTheory\Post2Post;

use Respect\Validation\Validator;
use WebTheory\Leonidas\Fields\Selections\PostQueryChecklistItems;
use WebTheory\Saveyour\Contracts\DataTransformerInterface;
use WebTheory\Saveyour\Contracts\FieldDataManagerInterface;
use WebTheory\Saveyour\Contracts\FormFieldControllerInterface;
use WebTheory\Saveyour\Contracts\FormFieldInterface;
use WebTheory\Saveyour\Controllers\AbstractField;
use WebTheory\Saveyour\Fields\Checklist;

class FormField extends AbstractField implements FormFieldControllerInterface
{
    use HasContextArgumentTrait;

    /**
     * @var PostRelationshipInterfaceInterface
     */
    protected $relationship;

    /**
     * @var string
     */
    protected $context;

    /**
     * @var array
     */
    protected $options = [];

    /**
     *
     */
    public function __construct(string $requestVar, Relationship $relationship, string $context, array $options = [])
    {
        $this->relationship = $relationship;

        $this->context = $this->throwExceptionIfInvalidContext($context, $relationship);
        $this->options = $this->defineOptions($options);

        parent::__construct($requestVar);
    }

    /**
     *
     */
    protected function defineOptions(array $options)
    {
        $relatedPostsType = $this->relationship->getRelatedPostTypeName($this->context);

        return [
            'id' => $options['id'] ?? "related-{$relatedPostsType}-checklist",
            'class' => $options['class'] ?? []
        ];
    }

    /**
     *
     */
    protected function createFormField(): ?FormFieldInterface
    {
        $options = $this->options;
        $selection = new PostQueryChecklistItems(
            $this->relationship->getRelatedPostTypePostsQuery($this->context)
        );

        return (new Checklist)
            ->setChecklistItemProvider($selection)
            ->setId($options['id'])
            ->setClasslist($options['class'])
            ->addClass('thing');
    }

    /**
     *
     */
    public function createDataManager(): ?FieldDataManagerInterface
    {
        return new TermRelatedPostsManager($this->relationship);
    }

    /**
     *
     */
    protected function createDataTransformer(): ?DataTransformerInterface
    {
        return new RelationshipToChecklistDataTransformer();
    }

    /**
     *
     */
    protected function defineFilters(): ?array
    {
        return ['sanitize_text_field'];
    }

    /**
     *
     */
    protected function defineRules(): ?array
    {
        return [
            'int' => [
                'validator' => Validator::intVal(),
                'alert' => 'A selection is invalid'
            ]
        ];
    }
}
