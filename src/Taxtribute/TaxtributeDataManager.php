<?php

namespace WebTheory\Taxtribute;

use Psr\Http\Message\ServerRequestInterface;
use WebTheory\Leonidas\Traits\ExpectsPostTrait;
use WebTheory\Saveyour\Contracts\FieldDataManagerInterface;

class TaxtributeDataManager implements FieldDataManagerInterface
{
    use ExpectsPostTrait;

    /**
     * @var Taxtribute
     */
    protected $model;

    /**
     * @var string
     */
    protected $attribute;

    /**
     *
     */
    public function __construct(string $attribute, Taxtribute $model)
    {
        $this->attribute = $attribute;
        $this->model = $model;
    }

    /**
     *
     */
    public function getCurrentData(ServerRequestInterface $request)
    {
        return $this->model->getValue($this->getPostId($request), $this->attribute);
    }

    /**
     *
     */
    public function handleSubmittedData(ServerRequestInterface $request, $data): bool
    {
        $post = $this->getPostId($request);
        $old = $this->model->getValue($post, $this->attribute);
        $updated = $old !== $data;

        if ($updated && $this->model->hasAttribute($post, $this->attribute)) {
            $this->model->setValue($post, $this->attribute, $data);
        }

        return $updated;
    }
}
