<?php

namespace App\Command;

use App\Entity\WordInput;
use App\Repository\WordResultRepository;
use App\Service\ArgsHandler;
use App\Service\InputReader;
use App\Service\OutputWriter;
use App\Service\Hyphenator;
use App\Service\PsrLogger\LoggerInterface;

class InteractiveInput implements CommandInterface
{
     // CLI args:
    const ARG_INPUT = 'input'; // -i, optional. Initial input. After processing it, will continue in interactive mode
        
    private InputReader $reader;
    private LoggerInterface $logger;
    private ArgsHandler $argsHandler;
    private Hyphenator $hyphenator;
    private OutputWriter $writer;
    private WordResultRepository $wordRepo;
    
    public function __construct(
        InputReader $reader,
        LoggerInterface $logger,
        ArgsHandler $argsHandler,
        Hyphenator $hyphenator,
        OutputWriter $writer,
        WordResultRepository $wordRepo
    ) {
        $this->reader = $reader;
        $this->logger = $logger;
        $this->argsHandler = $argsHandler;
        $this->hyphenator = $hyphenator;
        $this->writer = $writer;
        $this->wordRepo = $wordRepo;
        $argsHandler->addArgConfig(self::ARG_INPUT, 'i', false);
    }
    
    public function process(): void
    {
        $initialWordDone = false; // support word passed as cli arg
        
        while (true) {
            echo 'Enter a word: ';
            $word = '';
            if (!$initialWordDone) { 
                $initialWordDone = true;
                
                if ($this->argsHandler->isSet(self::ARG_INPUT)) {
                    $word = $this->argsHandler->get(self::ARG_INPUT);
                    echo $word."\n";
                }
                else
                    continue;
            } else {
                $word = readline();
            }
            $this->processOneWord($word);
        }
    }
    
    private function processOneWord(string $input): void
    {
        $wordResult = $this->wordRepo->findOne($input);
        if ($wordResult !== null) {
            $this->writer->printMinimalWordResult($wordResult);
        } else {
            $this->logger->debug('New word "%s", adding to DB', [$input]);
            [$array, $tree] = $this->reader->getPatternMatchers('array');
            $wordResult = $this->hyphenator->wordToSyllables(new WordInput($input), $array, $tree);
            $this->writer->printFullWordResult($wordResult);
            // TODO add to DB
        }
    }
}
