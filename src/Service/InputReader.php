<?php

namespace App\Service;

use App\DataStructure\HashTable;
use App\DataStructure\TextSearchInterface;
use App\Entity\HyphenationPattern;
use App\DataStructure\Trie\Trie;
use App\Entity\WordInput;
use App\Repository\HyphenationPatternRepository;
use Exception;
use Psr\Log\LoggerInterface;
use SplFileObject;

class InputReader
{
    const PATTERNS_FILE = __DIR__.'/../../data/text-hyphenation-patterns.txt';
    const WORDS_FILE = __DIR__.'/../../data/test-dictionary-140k.txt';
    
    private ArgsHandler $argsHandler;
    private LoggerInterface $logger;
    private HyphenationPatternRepository $patternRepo;
    
    private ?HashTable $patternHashTable = null;
    private ?Trie $patternTree = null;
    
    public function __construct(ArgsHandler $argsHandler, LoggerInterface $logger, HyphenationPatternRepository $patternRepo)
    {
        $this->argsHandler = $argsHandler;
        $this->logger = $logger;
        $this->patternRepo = $patternRepo;
    }
    
    /**
     * Get patterns as array
     *
     * @param  bool   $useDb    Whether to read patterns from DB or from file
     * @param  string $filePath
     * @return array<HyphenationPattern>
     */
    public function getPatternArray(bool $useDb = true, string $filePath = self::PATTERNS_FILE): array
    {
        if ($useDb) {
            return $this->patternRepo->getAll();
        } else {
            $patterns = [];
            $this->readPatternsFile(
                $filePath,
                function (string $line) use (&$patterns) {
                    $patterns[] = new HyphenationPattern($line);
                }
            );
            
            return $patterns;
        }
    }
    
    /**
     * Get patterns as searchable HashTable
     *
     * @param  bool   $useDb    Whether to read patterns from DB or from file
     * @param  string $filePath
     * @return HashTable HashTable initialized with patterns
     */
    public function getPatternHashTable(bool $useDb = true, string $filePath = self::PATTERNS_FILE): HashTable
    {
        if ($this->patternHashTable === null) {
            $this->patternHashTable = HashTable::constructFromArray(
                $this->getPatternArray($useDb, $filePath)
            );
        }
    
        return $this->patternHashTable;
    }
    
    /**
     * Get patterns as tree
     *
     * @param  bool   $useDb
     * @param  string $filePath
     * @return Trie
     */
    public function getPatternTree(bool $useDb = true, string $filePath = self::PATTERNS_FILE): Trie
    {
        if ($this->patternTree !== null) {
            return $this->patternTree;
        }
    
        $array = $this->getPatternArray($useDb, $filePath);
        
        Profiler::start("tree build");
        $tree = new Trie();
    
        foreach ($array as $p) {
            $tree->addValue($p->getPatternNoNumbers(), $p);
        }
        
        $this->logger->debug('Tree built in %f ms', [Profiler::stop("tree build")]);
        
        $this->patternTree = $tree;
        return $tree;
    }
    
    /**
     * Get HashTable or Tree for pattern search.
     * Data structure chosen based on CLI arg or $defaultMethod
     *
     * @param  string $defaultDS class name of default data structure to choose
     *                           if CLI arg isn't provided
     * @param  bool   $useDb     If true, will read patterns from DB. If False, will read from default file.
     * @return TextSearchInterface
     */
    public function getPatternSearchDS(string $defaultDS, bool $useDb = true): TextSearchInterface
    {
        // convert class names and new 'hashtable' option to old format 
        $defaultDS = match ($defaultDS) {
            Trie::class => 'tree',
            HashTable::class, 'hashtable' => 'array',
        };
        
        if ($this->argsHandler->get('method', $defaultDS) === 'tree') {
            return $this->getPatternTree($useDb);
        } else {
            return $this->getPatternHashTable($useDb);
        }
    }
    
    /**
     * Clear cached pattern array and tree to force rebuild them on next getPattern* call
     */
    public function clearPatternsCache(): void
    {
        $this->patternHashTable = null;
        $this->patternTree = null;
    }
    
    /**
     * Read pattern file with callback for each pattern
     *
     * @param string $path
     * @param $callback callable Will get called for each pattern, with pattern as the only argument
     */
    private function readPatternsFile(string $path, callable $callback): void
    {
        $file = new SplFileObject($path);
    
        while (!$file->eof())
        {
            $line = trim($file->fgets());
            $callback($line);
        }
    }
    
    /**
     * Get word list for batch processing
     *
     * @param  string $filePath
     * @return array<WordInput>
     */
    public function getWordList(string $filePath = self::WORDS_FILE): array // TODO remove
    {
        // TODO fix multibyte encoding when reading from file
        
        $file = new SplFileObject($filePath, 'r');
        $words = [];
        
        while (!$file->eof())
        {
            $line = trim($file->fgets());
            $word = explode(',', $line);
            $words[] = new WordInput($word[0], $word[1]);
        }
        
        return $words;
    }
    
}