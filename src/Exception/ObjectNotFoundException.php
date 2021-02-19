<?php

namespace App\Exception;

use Throwable;

/**
 * Class, method, namespace or other object not found.
 *
 * @package App\Exception
 */
class ObjectNotFoundException extends \Exception implements HttpResponseExceptionInterface
{
    private int $status;
    
    public function __construct($message = "", int $status = 500, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->status = $status;
    }
    
    public function getStatus(): int
    {
        return $this->status;
    }
}
