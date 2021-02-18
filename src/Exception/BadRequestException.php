<?php

namespace App\Exception;

use Throwable;

/**
 * Invalid or malformed request
 *
 * @package App\Exception
 */
class BadRequestException extends \Exception implements HttpResponseExceptionInterface
{
    private int $status;
    
    public function __construct($message = "", int $status = 400, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->status = $status;
    }
    
    public function getStatus(): int
    {
        return $this->status;
    }
}
