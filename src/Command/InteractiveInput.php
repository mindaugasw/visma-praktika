<?php

namespace App\Command;

use App\Entity\WordInput;
use App\Service\ArgsHandler;
use App\Service\InputReader;
use App\Service\OutputWriter;
use App\Service\Hyphenator;

class InteractiveInput implements CommandInterface
{
     // CLI args:
    const ARG_INPUT = 'input'; // -i, optional. Initial input. After processing it, will continue in interactive mode
        
    private InputReader $reader;
    private ArgsHandler $argsHandler;
    private Hyphenator $hyphenator;
    private OutputWriter $writer;
    
    public function __construct(
        InputReader $reader,
        ArgsHandler $argsHandler,
        Hyphenator $hyphenator,
        OutputWriter $writer
    ) {
        $this->reader = $reader;
        $this->argsHandler = $argsHandler;
        $this->hyphenator = $hyphenator;
        $this->writer = $writer;
        $argsHandler->addArgConfig(self::ARG_INPUT, 'i', false);
    }
    
    public function process(): void
    {
        [$array, $tree] = $this->reader->getPatternMatchers('array');
        $initialWordDone = false; // support word passed as cli arg
        
        while (true) {
            $word = '';
            if (!$initialWordDone) { 
                $initialWordDone = true;
                
                if ($this->argsHandler->isSet(self::ARG_INPUT))
                    $word = $this->argsHandler->get(self::ARG_INPUT);
                else
                    continue;
            } else {
                $word = readline('Enter a word: ');
            }
            
            $res = $this->hyphenator->wordToSyllables(new WordInput($word), $array, $tree);
            $this->writer->printOneWordResultToConsole($res);
        }
    }
}
