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
     * @var Model $relationship
     */
    protected $relationship;

    /**
     *
     */
    public function __construct(Model $relationship)
    {
        $this->relationship = $relationship;
    }

    /**
     *
     */
    public function getCurrentData(ServerRequestInterface $request)
    {
        return $this->relationship->getRelatedPosts($request->getAttribute('post'));
    }

    /**
     *
     */
    public function handleSubmittedData(ServerRequestInterface $request, $posts): bool
    {
        $post = $request->getAttribute('post');
        $old = $this->relationship->getRelatedPosts($post);

        if (isset($posts['set'])) {
            $this->relationship->setPostRelationships($post, ...$posts['set']);
        }

        if (isset($posts['unset'])) {
            $this->relationship->unsetPostRelationships($post, ...$posts['unset']);
        }

        $new = $this->relationship->getRelatedPosts($post);

        return $this->relationshipsUpdated($old, $new);
    }

    /**
     *
     */
    protected function relationshipsUpdated(array $array1, array $array2): bool
    {
        $diff = false;

        if (!empty($array1)) {
            $cb = function (WP_Post $post1, WP_Post $post2) {
                return $post1->ID - $post2->ID;
            };

            $diff = (bool) array_udiff($array1, $array2, $cb);
        } elseif (count($array1) !== count($array2)) {
            $diff = true;
        }

        return $diff;
    }
}
