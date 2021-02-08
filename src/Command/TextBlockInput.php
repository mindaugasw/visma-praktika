<?php

namespace App\Command;

use App\Entity\WordInput;
use App\Service\FileHandler;
use App\Service\InputReader;
use App\Service\SyllablesAlgorithm;

class TextBlockInput implements CommandInterface
{
    const REGEX_WORD_SEPARATOR = '/\b[a-zA-Z\-\'’]{2,}\b/'; // ' needs additional escaping?
    
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
        $inputStr = '';
        if ($this->reader->getArg_singleInput() !== null) {
            $inputStr = $this->reader->getArg_singleInput();
        } else if ($this->reader->getArg_fileInput() !== null) {
            $path = $this->reader->getArg_fileInput();
    
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
        if ($this->reader->getArg_fileOutput() !== null) {
            $path = $this->reader->getArg_fileOutput();
            $file = $this->fileHandler->openWithMkdir($path, 'x');
            
            $file->fwrite($text);
            echo sprintf("Output saved to \"%s\"\n", $path);
        } else {
            echo $text."\n";
        }
    }
}
