<?php

namespace WebTheory\Post2Post\Exceptions;

use InvalidArgumentException;

class InvalidContextArgumentException extends InvalidArgumentException
{
    /**
     *
     */
    protected $message = '$context must either be "related" or "relatable".';

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }
}
