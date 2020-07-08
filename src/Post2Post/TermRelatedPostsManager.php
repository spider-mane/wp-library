<?php

namespace WebTheory\Post2Post;

use Psr\Http\Message\ServerRequestInterface;
use WP_Post;
use WebTheory\Leonidas\Traits\ExpectsPostTrait;
use WebTheory\Leonidas\Util\PostCollection;
use WebTheory\Saveyour\Contracts\FieldDataManagerInterface;

class TermRelatedPostsManager implements FieldDataManagerInterface
{
    use ExpectsPostTrait;

    /**
     * The somewhat relatable post types object
     *
     * @var Relationship $relationship
     */
    protected $relationship;

    /**
     *
     */
    public function __construct(PostRelationshipInterfaceInterface $relationship)
    {
        $this->relationship = $relationship;
    }

    /**
     *
     */
    public function getCurrentData(ServerRequestInterface $request)
    {
        return $this->relationship->getRelatedPosts($this->getPost($request));
    }

    /**
     * @param WP_Post[] $relatedPosts
     */
    public function handleSubmittedData(ServerRequestInterface $request, $relatedPosts): bool
    {
        $post = $this->getPost($request);
        $old = new PostCollection(...$this->relationship->getRelatedPosts($post));
        $new = new PostCollection(...$relatedPosts);

        $this->relationship->setPostRelationships($post, ...$relatedPosts);

        return $old->isDiff($new);
    }
}
