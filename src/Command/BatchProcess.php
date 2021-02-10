<?php

namespace App\Command;

use App\Exception\NotImplementedException;
use App\Service\FileHandler;
use App\Service\InputReader;
use App\Service\Hyphenator;

class BatchProcess implements CommandInterface
{
    private InputReader $reader;
    private Hyphenator $hyphenator;
    private FileHandler $fileHandler;
    
    public function __construct(InputReader $reader, Hyphenator $hyphenator, FileHandler $fileHandler)
    {
        $this->reader = $reader;
        $this->hyphenator = $hyphenator;
        $this->fileHandler = $fileHandler;
    }
    
    public function process(): void
    {
        throw new NotImplementedException();
    }
}
