<?php

namespace WebTheory\Post2Post;

use WebTheory\Leonidas\Fields\Selections\PostQueryChecklistItems;
use WebTheory\Saveyour\Contracts\DataTransformerInterface;
use WebTheory\Saveyour\Contracts\FormFieldControllerInterface;
use WebTheory\Saveyour\Contracts\FormFieldInterface;
use WebTheory\Saveyour\Fields\Checklist;

class PostRelationshipChecklist extends AbstractPostRelationshipFormField implements FormFieldControllerInterface
{
    /**
     * @var array
     */
    protected $options = [];

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
    protected function createDataTransformer(): ?DataTransformerInterface
    {
        return new RelationshipToChecklistDataTransformer();
    }
}
