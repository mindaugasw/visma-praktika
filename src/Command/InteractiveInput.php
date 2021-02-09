<?php

namespace App\Command;

use App\Entity\WordInput;
use App\Service\ArgsParser;
use App\Service\InputReader;
use App\Service\OutputWriter;
use App\Service\SyllablesAlgorithm;

class InteractiveInput implements CommandInterface
{
     // CLI args:
    const ARG_INPUT = 'input'; // -i, optional. Initial input. After processing it, will continue in interactive mode
        
    private InputReader $reader;
    private ArgsParser $argsParser;
    private SyllablesAlgorithm $alg;
    private OutputWriter $writer;
    
    public function __construct(
        InputReader $reader,
        ArgsParser $argsParser,
        SyllablesAlgorithm $alg,
        OutputWriter $writer
    ) {
        $this->reader = $reader;
        $this->argsParser = $argsParser;
        $this->alg = $alg;
        $this->writer = $writer;
        $argsParser->addArgConfig(self::ARG_INPUT, 'i', false);
    }
    
    public function process(): void
    {
        [$array, $tree] = $this->reader->getPatternMatchers('array');
        $initialWordDone = false; // support word passed as cli arg
        
        while (true) {
            $word = '';
            if (!$initialWordDone) { 
                $initialWordDone = true;
                
                if ($this->argsParser->isSet(self::ARG_INPUT))
                    $word = $this->argsParser->get(self::ARG_INPUT);
                else
                    continue;
            } else {
                $word = readline('Enter a word: ');
            }
            
            $res = $this->alg->wordToSyllables(new WordInput($word), $array, $tree);
            $this->writer->printOneWordResultToConsole($res);
        }
    }
}
