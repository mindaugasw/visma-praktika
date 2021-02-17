<?php

namespace App\Exception;

use Throwable;

/**
 * Requested entity object was not found in DB.
 * @package App\Exception
 */
class EntityNotFoundException extends \Exception implements HttpResponseExceptionInterface
{
    private int $status;
    
    public function __construct($message = "", int $status = 404, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->status = $status;
    }
    
    public function getStatus(): int
    {
        return $this->status;
    }
}
