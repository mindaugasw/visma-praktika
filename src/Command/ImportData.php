<?php

namespace App\Command;

use App\Repository\HyphenationPatternRepository;
use App\Service\ArgsParser;
use App\Service\DBConnection;
use App\Service\InputReader;
use App\Service\PsrLogger\Logger;
use App\Service\PsrLogger\LoggerInterface;
use Exception;

class ImportData implements CommandInterface
{
    // CLI Args:
    /**
     * --patterns, -p, optional. Patterns import options 
     * Values:
     * - true - import default patterns file, default option
     * - false - skip pattern import
     * - file path - import specific file
     */
    const ARG_PATTERNS_FILE = 'patterns';
    /**
     * --words, -w, optional. Words import options
     * Values:
     * - true - import default words file, default option
     * - false - skip word import
     * - file path - import specific file
     */
    const ARG_WORDS_FILE = 'words';
    
    private DBConnection $db;
    private ArgsParser $argsParser;
    private InputReader $reader;
    private LoggerInterface $logger;
    private HyphenationPatternRepository $patternRepo;
    
    public function __construct(
        DBConnection $db,
        ArgsParser $argsParser,
        InputReader $reader,
        LoggerInterface $logger,
        HyphenationPatternRepository $patternRepo
    ) {
        $this->db = $db;
        $this->argsParser = $argsParser;
        $this->reader = $reader;
        $this->logger = $logger;
        $this->patternRepo = $patternRepo;
    
        $argsParser->addArgConfig(self::ARG_PATTERNS_FILE, 'p');
        $argsParser->addArgConfig(self::ARG_WORDS_FILE, 'w');
    }
    
    public function process(): void
    {
        $this->importPatterns();
    
        if ($this->argsParser->isSet(self::ARG_WORDS_FILE))
            $this->importWords($this->argsParser->get(self::ARG_WORDS_FILE));
    }
    
    private function importPatterns(): void
    {
        $argVal = $this->argsParser->get(self::ARG_PATTERNS_FILE, 'true');
        $patterns = [];
        
        if ($argVal === 'false') {
            $this->logger->info('Skipping pattern import');
            return;
        } else if ($argVal === 'true') {
            $this->logger->info('Importing default pattern file');
            $patterns = $this->reader->getPatternArray();
        } else {
            $this->logger->info('Importing custom pattern file');
            if (!file_exists($argVal))
                throw new Exception(sprintf('File does not exist: "%s"', $argVal));
            $patterns = $this->reader->getPatternArray($argVal);
        }
        
        $this->patternRepo->import($patterns);
        
    }
    
    private function importWords(string $path): void
    {
        if (!file_exists($path))
            throw new Exception(sprintf('File does not exist: "%s"', $path));
        
        
    }
    
}
