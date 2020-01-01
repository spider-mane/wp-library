<?php

namespace WebTheory\Taxtrubute;

use Psr\Http\Message\ServerRequestInterface;
use WebTheory\Leonidas\Fields\Managers\PostMetaFieldManager;
use WebTheory\Saveyour\Contracts\FieldDataManagerInterface;
use WebTheory\Saveyour\Request;

class TermBasedPostMeta extends PostMetaFieldManager implements FieldDataManagerInterface
{
    /**
     * The term used as attribute key
     * 
     * @var string
     */
    protected $attribute;

    /**
     * @var string
     */
    protected $taxonomy;

    /**
     * @var string
     */
    protected $deleteButton;

    /**
     *
     */
    public function __construct(string $metaKey, string $taxonomy, string $attrubute)
    {
        $this->metaKey = $metaKey;
        $this->taxonomy = $taxonomy;
        $this->attribute = $attrubute;
    }

    /**
     * Get the value of attribute
     *
     * @return mixed
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set the value of attribute
     *
     * @param mixed $attribute
     *
     * @return self
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Get the value of deleteButton
     *
     * @return mixed
     */
    public function getDeleteButton()
    {
        return $this->deleteButton;
    }

    /**
     * Set the value of deleteButton
     *
     * @param mixed $deleteButton
     *
     * @return self
     */
    public function setDeleteButton(string $deleteButton)
    {
        $this->deleteButton = $deleteButton;

        return $this;
    }

    /**
     *
     */
    public function handleSubmittedData(ServerRequestInterface $request, $data): bool
    {
        $post = $request->getAttribute('post');
        $response = false;

        switch (true) {
            case isset($this->deleteButton) && Request::has($request, $this->deleteButton):
                $this->deleteData($post);
                $response = true;
                break;

            case has_term($this->attribute, $this->taxonomy, $post->ID):
                $response = parent::handleSubmittedData($request, $data);
                break;
        }

        return $response;
    }

    /**
     *
     */
    protected function deleteData($post)
    {
        parent::deleteData($post);
        wp_remove_object_terms($post->ID, $this->attribute, $this->taxonomy);
    }
}
