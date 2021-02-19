<?php

namespace App\Controller;

use App\Exception\BadRequestException;
use App\Service\Response\JsonErrorResponse;
use App\Service\Response\ResponseHandler;

abstract class BaseController
{
    
    public function __construct()
    {
    }
    
    /**
     * Get arg by $key or $default value if it's not set.
     * If $isRequired=true and arg isn't set, will throw exception instead of
     * return $default value
     *
     * @param  array  $args
     * @param  string $key        arg to search for
     * @param  mixed  $default    default value if $key isn't found
     * @param  bool   $isRequired if true, will throw exception if arg isn't found
     * @return mixed
     */
    protected function getArgOrDefault(
        array $args,
        string $key,
        mixed $default = null,
        bool $isRequired = true
    ): mixed {
        if (isset($args[$key])) {
            return $args[$key];
        } elseif ($isRequired) {
            throw new BadRequestException(
                sprintf('Required query argument \'%s\' not found', $key)
            );
        } else {
            return $default;
        }
    }
}
