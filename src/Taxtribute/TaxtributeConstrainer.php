<?php

namespace WebTheory\Taxtribute;

use Psr\Http\Message\ServerRequestInterface;
use WebTheory\Leonidas\Contracts\ComponentConstrainerInterface;
use WebTheory\Leonidas\Traits\ExpectsPostTrait;

class TaxtributeConstrainer implements ComponentConstrainerInterface
{
    use ExpectsPostTrait;

    /**
     * @var string;
     */
    protected $attribute;

    /**
     * @var Taxtribute
     */
    protected $taxtribute;

    /**
     *
     */
    public function __construct(string $attribute, Taxtribute $taxtribute)
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
