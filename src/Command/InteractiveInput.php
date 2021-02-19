<?php

namespace App\Command;

use App\DataStructure\HashTable;
use App\Entity\WordInput;
use App\Repository\WordResultRepository;
use App\Service\ArgsHandler;
use App\Service\Hyphenator\HyphenationHandler;
use App\Service\InputReader;
use App\Service\OutputWriter;
use App\Service\Hyphenator\Hyphenator;
use Psr\Log\LoggerInterface;

class InteractiveInput implements CommandInterface
{
    // CLI arg keys:
    /**
     * --input, -i, optional. Initial word input. After processing it, will
     * continue in interactive mode
     */
    private const ARG_INPUT = 'input';
        
    public function __construct(
        private LoggerInterface $logger,
        private ArgsHandler $argsHandler,
        private HyphenationHandler $hyphenator,
        private OutputWriter $writer,
    ) {
        $argsHandler->addArgConfig(self::ARG_INPUT, 'i', false);
    }
    
    public function process(): void
    {
        $initialWordDone = false; // support word passed as cli arg
        
        while (true) {
            echo 'Enter a word: ';
            
            if (!$initialWordDone) {
                $initialWordDone = true;
                
                if ($this->argsHandler->isSet(self::ARG_INPUT)) {
                    $word = $this->argsHandler->get(self::ARG_INPUT);
                    echo $word . "\n";
                } else {
                    continue;
                }
            } else {
                $word = readline();
            }
            if (empty($word)) {
                continue;
            }
            
            $word = strtolower($word); // TODO fix algorithm to ignore casing
            $this->processOneWord($word);
        }
    }
    
    private function processOneWord(string $input): void
    {
        $wordResult = $this->hyphenator->processOneWord($input);
        
        if (empty($wordResult->getNumberMatrix())) {
            // word from db, number matrix not initialized
            $this->writer->printMinimalWordResult($wordResult);
        } else {
            // newly hyphenated word with full info
            $this->logger->debug('New word "%s", adding to DB', [$input]);
            $this->writer->printFullWordResult($wordResult);
        }
    }
}
