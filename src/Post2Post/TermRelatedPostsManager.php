<?php

namespace WebTheory\Post2Post;

use Psr\Http\Message\ServerRequestInterface;
use WP_Post;
use WebTheory\Saveyour\Contracts\FieldDataManagerInterface;

class TermRelatedPostsManager implements FieldDataManagerInterface
{
    /**
     * The somewhat relatable post types object
     *
     * @var Relationship $relationship
     */
    protected $relationship;

    /**
     *
     */
    public function __construct(Relationship $relationship)
    {
        $this->relationship = $relationship;
    }

    /**
     *
     */
    protected function getPost(ServerRequestInterface $request)
    {
        return $request->getAttribute('post') ?? get_post();
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
