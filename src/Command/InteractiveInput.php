<?php

namespace App\Command;

use App\Entity\WordInput;
use App\Service\InputReader;
use App\Service\OutputWriter;
use App\Service\SyllablesAlgorithm;

class InteractiveInput implements CommandInterface
{
    private InputReader $reader;
    private SyllablesAlgorithm $alg;
    private OutputWriter $writer;
    
    public function __construct(InputReader $reader, SyllablesAlgorithm $alg, OutputWriter $writer)
    {
        $this->reader = $reader;
        $this->alg = $alg;
        $this->writer = $writer;
    }
    
    public function process(): void
    {
        [$array, $tree] = $this->reader->getPatternMatchers('array');
        $initialWordDone = false; // support word passed as cli arg
        
        while (true) {
            $word = '';
            if (!$initialWordDone) { 
                $initialWordDone = true;
                
                $word = $this->reader->getArg_singleInput();
                if ($word === null)
                    continue;
            } else {
                $word = readline('Enter a word: ');
            }
            
            $res = $this->alg->wordToSyllables(new WordInput($word), $array, $tree);
            $this->writer->printOneWordResultToConsole($res);
        }
    }
}
