<?php

namespace WebTheory\Post2Post;

use Respect\Validation\Validator;
use WP_Post;
use WebTheory\Leonidas\Fields\WpAdminField;
use WebTheory\Saveyour\Fields\Checklist;

class FormField extends WpAdminField
{
    use HasContextArgumentTrait;

    /**
     * @var Model
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
    public function __construct(string $requestVar, string $context, Model $relationship, array $options = null)
    {
        $this->context = $this->throwExceptionIfInvalidContext($context);
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
        $posts = $this->relationship->getPostsFor($this->context);
        $items = $this->getChecklistItemsFromPosts($posts);

        return (new Checklist)
            ->setId("wts--{$this->relationship->getName()}--checklist")
            ->setItems($items)
            ->setToggleControl('0')
            ->addClass('thing');
    }

    /**
     * @param WP_Post[] $posts
     */
    protected function getChecklistItemsFromPosts($posts)
    {
        $items = [];

        foreach ($posts as $post) {
            $items[$post->post_name] = [
                'value' => '1',
                'label' => $post->post_title,
                'name' => (string) $post->ID,
                'id' => "wts--{$post->post_name}",
            ];
        }

        return $items;
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
        return new PostsToChecklistDataTransformer();
    }
}
