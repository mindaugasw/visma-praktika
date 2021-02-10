<?php

namespace App\Command;

use App\Entity\WordInput;
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
    
    public function __construct(InputReader $reader, ArgsHandler $argsHandler, Hyphenator $hyphenator, FileHandler $fileHandler)
    {
        $this->reader = $reader;
        $this->argsHandler = $argsHandler;
        $this->hyphenator = $hyphenator;
        $this->fileHandler = $fileHandler;
    
        $argsHandler->addArgConfig(self::ARG_CLI_INPUT, 'i');
        $argsHandler->addArgConfig(self::ARG_FILE_INPUT, 'f');
        $argsHandler->addArgConfig(self::ARG_FILE_OUTPUT, 'o');
    }
    
    public function process(): void
    {
        $inputStr = '';
        if ($this->argsHandler->isSet(self::ARG_CLI_INPUT)) {
            $inputStr = $this->argsHandler->get(self::ARG_CLI_INPUT);
        } else if ($this->argsHandler->isSet(self::ARG_FILE_INPUT)) {
            $path = $this->argsHandler->get(self::ARG_FILE_INPUT);
    
            if (!file_exists($path))
                throw new \Exception(sprintf('File does not exist: "%s"', $path));
            
            $inputStr = file_get_contents($path);
        } else
            throw new \Exception('No input provided');
        
        $text = $this->processText($inputStr);
        $this->writeOutput($text);
    }
    
    private function processText(string $text): string
    {
        [$array, $tree] = $this->reader->getPatternMatchers('tree');
    
        return preg_replace_callback(
            self::REGEX_WORD_SEPARATOR,
            function ($matches) use ($array, $tree) {
                return $this->hyphenator->wordToSyllables(
                    new WordInput($matches[0]),
                    $array,
                    $tree)
                    ->getResult();
            },
            $text);
    }
    
    private function writeOutput(string $text): void
    {
        if ($this->argsHandler->isSet(self::ARG_FILE_OUTPUT)) {
            $path = $this->argsHandler->get(self::ARG_FILE_OUTPUT);
            $file = $this->fileHandler->openWithMkdir($path, 'x');
            
            $file->fwrite($text);
            echo sprintf("Output saved to \"%s\"\n", $path);
        } else {
            echo $text."\n";
        }
    }
}
