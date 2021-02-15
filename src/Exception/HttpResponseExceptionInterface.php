<?php

namespace App\Exception;

interface HttpResponseExceptionInterface
{
    public function getMessage();
    public function getStatus(): int;
}
