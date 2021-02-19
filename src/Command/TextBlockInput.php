<?php

namespace App\Command;

use App\Entity\WordInput;
use App\Repository\WordResultRepository;
use App\Service\ArgsHandler;
use App\Service\FileHandler;
use App\Service\InputReader;
use App\Service\Hyphenator;

class TextBlockInput implements CommandInterface
{
    // CLI args:
    const ARG_CLI_INPUT = 'input'; // -i, optional. Text block input as text
    const ARG_FILE_INPUT = 'file'; // -f, optional. Input as file path
    // Either --input or --file must be set
    const ARG_FILE_OUTPUT = 'output'; // -o, optional. Output file path. If not set, will write output to the console
    
    const REGEX_WORD_SEPARATOR = '/\b[a-zA-Z\-\'’]{2,}\b/'; // ' needs additional escaping?
    
    private InputReader $reader;
    private ArgsHandler $argsHandler;
    private Hyphenator $hyphenator;
    private FileHandler $fileHandler;
    private WordResultRepository $wordRepo;
    
    public function __construct(
        InputReader $reader,
        ArgsHandler $argsHandler,
        Hyphenator $hyphenator,
        FileHandler $fileHandler,
        WordResultRepository $wordRepo
    ) {
        $this->reader = $reader;
        $this->argsHandler = $argsHandler;
        $this->hyphenator = $hyphenator;
        $this->fileHandler = $fileHandler;
        $this->wordRepo = $wordRepo;
    
        $argsHandler->addArgConfig(self::ARG_CLI_INPUT, 'i');
        $argsHandler->addArgConfig(self::ARG_FILE_INPUT, 'f');
        $argsHandler->addArgConfig(self::ARG_FILE_OUTPUT, 'o');
    }
    
    public function process(): void
    {
        $inputStr = '';
        if ($this->argsHandler->isSet(self::ARG_CLI_INPUT)) {
            $inputStr = $this->argsHandler->get(self::ARG_CLI_INPUT);
        } elseif ($this->argsHandler->isSet(self::ARG_FILE_INPUT)) {
            $path = $this->argsHandler->get(self::ARG_FILE_INPUT);
    
            if (!file_exists($path)) {
                throw new \Exception(sprintf('File does not exist: "%s"', $path));
            }
            
            $inputStr = file_get_contents($path);
        } else {
            throw new \Exception('No input provided');
        }
        
        $inputStr = strtolower($inputStr); // TODO fix algorithm to ignore casing
        $text = $this->processText($inputStr);
        $this->writeOutput($text);
    }
    
    private function processText(string $text): string
    {
        [$array, $tree] = $this->reader->getPatternMatchers('tree');
    
        $wordMatches = []; // words with their positions in text [[word, pos], ...]
        preg_match_all(self::REGEX_WORD_SEPARATOR, $text, $wordMatches, PREG_OFFSET_CAPTURE);
        $wordMatches = $wordMatches[0]; // remove extra nesting
    
        // map to 1D array and remove match position data
        $wordInputs = array_map(
            function ($match) {
                return $match[0];
            },
            $wordMatches
        );
        $wordResults = $this->wordRepo->findMany($wordInputs);
        
        $originalTextLength = strlen($text);
        $newWords = []; // words found in $text but not in DB
        
        foreach ($wordMatches as $match) {
            // replace words found in DB
            if (array_key_exists($match[0], $wordResults)) {// match[0] - word input string
                $wordResult = $wordResults[$match[0]];
            } else { // hyphenate word and replace it
                $wordInput = new WordInput($match[0]);
                $wordResult = $this->hyphenator->wordToSyllables($wordInput, $array, $tree);
                $newWords[] = $wordResult;
            }
    
            $text = substr_replace(
                $text, // full text block, in which to replace
                $wordResult->getResult(), // new string to replace with
                strlen($text) - $originalTextLength + $match[1], // start index // accounts for moved index due already replaced words
                strlen($match[0]) // length of the input word
            );
        }
        
        if (count($newWords) !== 0) {
            $this->wordRepo->insertMany($newWords);
        }
        
        return $text;
    }
    
    private function writeOutput(string $text): void
    {
        if ($this->argsHandler->isSet(self::ARG_FILE_OUTPUT)) {
            $path = $this->argsHandler->get(self::ARG_FILE_OUTPUT);
            $file = $this->fileHandler->openWithMkdir($path, 'x');
            
            $file->fwrite($text);
            echo sprintf("Output saved to \"%s\"\n", $path);
        } else {
            echo $text . "\n";
        }
    }
}
