<?php

namespace App\Command;

use App\Service\ArgsHandler;
use App\Service\FileHandler;
use App\Service\Hyphenator\HyphenationHandler;
use App\Service\InputReader;
use Exception;

class TextBlockInput implements CommandInterface
{
    // CLI arg keys:
    /**
     * --input, -i, optional. Text block input as text
     * Either this or --file must be set
     */
    private const ARG_CLI_INPUT = 'input';
    /**
     * --file, -f, optional. Input as file path
     * Either this or --input must be set
     */
    private const ARG_FILE_INPUT = 'file';
    /**
     * --output, -o, optional. Output file path. All output will be written to
     * this file, or to console if file path not sed.
     */
    private const ARG_FILE_OUTPUT = 'output';
    
    public function __construct(
        private InputReader $reader,
        private ArgsHandler $argsHandler,
        private HyphenationHandler $hyphenationHandler,
        private FileHandler $fileHandler,
    ) {
        $argsHandler->addArgConfig(self::ARG_CLI_INPUT, 'i');
        $argsHandler->addArgConfig(self::ARG_FILE_INPUT, 'f');
        $argsHandler->addArgConfig(self::ARG_FILE_OUTPUT, 'o');
    }
    
    public function process(): void
    {
        if ($this->argsHandler->isSet(self::ARG_CLI_INPUT)) {
            $inputStr = $this->argsHandler->get(self::ARG_CLI_INPUT);
        } elseif ($this->argsHandler->isSet(self::ARG_FILE_INPUT)) {
            $path = $this->argsHandler->get(self::ARG_FILE_INPUT);
    
            if (!file_exists($path)) {
                throw new Exception(sprintf('File does not exist: "%s"', $path));
            }
            
            $inputStr = file_get_contents($path);
        } else {
            throw new Exception('No input provided');
        }
        
        $inputStr = strtolower($inputStr); // TODO fix algorithm to ignore casing
        $text = $this->hyphenationHandler->processText($inputStr);
        $this->writeOutput($text);
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
