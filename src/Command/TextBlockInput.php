<?php

namespace App\Command;

use App\Entity\WordInput;
use App\Service\ArgsParser;
use App\Service\FileHandler;
use App\Service\InputReader;
use App\Service\SyllablesAlgorithm;

class TextBlockInput implements CommandInterface
{
    /*
     * CLI args:
     * - input, -i, optional. Text block input as text
     * - file, -f, optional. Input as file path
     * Either -i or -f must be provided
     * - output, -o, optional. Output file path. If not set, will write output to the console 
     */
    const ARG_CLI_INPUT = 'input';
    const ARG_FILE_INPUT = 'file';
    const ARG_FILE_OUTPUT = 'output';
    
    const REGEX_WORD_SEPARATOR = '/\b[a-zA-Z\-\'’]{2,}\b/'; // ' needs additional escaping?
    
    private InputReader $reader;
    private ArgsParser $argsParser;
    private SyllablesAlgorithm $alg;
    private FileHandler $fileHandler;
    
    public function __construct(InputReader $reader, ArgsParser $argsParser, SyllablesAlgorithm $alg, FileHandler $fileHandler)
    {
        $this->reader = $reader;
        $this->argsParser = $argsParser;
        $this->alg = $alg;
        $this->fileHandler = $fileHandler;
    
        $argsParser->addArgConfig(self::ARG_CLI_INPUT, 'i');
        $argsParser->addArgConfig(self::ARG_FILE_INPUT, 'f');
        $argsParser->addArgConfig(self::ARG_FILE_OUTPUT, 'o');
    }
    
    public function process(): void
    {
        $inputStr = '';
        if ($this->argsParser->isSet(self::ARG_CLI_INPUT)) {
            $inputStr = $this->argsParser->get(self::ARG_CLI_INPUT);
        } else if ($this->argsParser->isSet(self::ARG_FILE_INPUT)) {
            $path = $this->argsParser->get(self::ARG_FILE_INPUT);
    
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
                return $this->alg->wordToSyllables(
                    new WordInput($matches[0]),
                    $array,
                    $tree)
                    ->getResult();
            },
            $text);
    }
    
    private function writeOutput(string $text): void
    {
        if ($this->argsParser->isSet(self::ARG_FILE_OUTPUT)) {
            $path = $this->argsParser->get(self::ARG_FILE_OUTPUT);
            $file = $this->fileHandler->openWithMkdir($path, 'x');
            
            $file->fwrite($text);
            echo sprintf("Output saved to \"%s\"\n", $path);
        } else {
            echo $text."\n";
        }
    }
}
