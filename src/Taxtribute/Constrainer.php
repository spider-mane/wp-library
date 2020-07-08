<?php

namespace WebTheory\Taxtribute;

use Psr\Http\Message\ServerRequestInterface;
use WP_Post;
use WebTheory\Leonidas\Contracts\ComponentConstrainerInterface;
use WebTheory\Leonidas\Traits\ExpectsPostTrait;

class Constrainer implements ComponentConstrainerInterface
{
    use ExpectsPostTrait;

    /**
     * @var string;
     */
    protected $attribute;

    /**
     * @var Model
     */
    protected $taxtribute;

    /**
     *
     */
    public function __construct(string $attribute, Model $taxtribute)
    {
        $this->attribute = $attribute;
        $this->taxtribute = $taxtribute;
    }

    /**
     *
     */
    public function screenMeetsCriteria(ServerRequestInterface $request): bool
    {
        return $this->taxtribute->hasAttribute(
            $this->getPostId($request),
            $this->attribute
        );
    }
}
