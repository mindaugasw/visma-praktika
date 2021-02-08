<?php

namespace App\Command;

use App\Exception\NotImplementedException;
use App\Service\FileHandler;
use App\Service\InputReader;
use App\Service\SyllablesAlgorithm;

class BatchProcess implements CommandInterface
{
    private InputReader $reader;
    private SyllablesAlgorithm $alg;
    private FileHandler $fileHandler;
    
    public function __construct(InputReader $reader, SyllablesAlgorithm $alg, FileHandler $fileHandler)
    {
        $this->reader = $reader;
        $this->alg = $alg;
        $this->fileHandler = $fileHandler;
    }
    
    public function process(): void
    {
        throw new NotImplementedException();
    }
}
