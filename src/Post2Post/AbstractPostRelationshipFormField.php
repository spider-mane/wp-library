<?php

namespace WebTheory\Post2Post;

use Respect\Validation\Validator;
use WebTheory\Saveyour\Contracts\DataTransformerInterface;
use WebTheory\Saveyour\Contracts\FieldDataManagerInterface;
use WebTheory\Saveyour\Contracts\FormFieldControllerInterface;
use WebTheory\Saveyour\Controllers\AbstractField;

abstract class AbstractPostRelationshipFormField extends AbstractField implements FormFieldControllerInterface
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
    public function __construct(string $requestVar, PostRelationship $relationship, string $context, array $options = [])
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
    public function createDataManager(): ?FieldDataManagerInterface
    {
        return new PostRelationshipDataManager($this->relationship);
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
