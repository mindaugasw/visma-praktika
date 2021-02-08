<?php

namespace App\Command;

use App\Exception\NotImplementedException;

class BatchProcess implements CommandInterface
{
    public function __construct()
    {
        throw new NotImplementedException();
    }
    
    public function process(): void
    {
        throw new NotImplementedException();
    }
}
