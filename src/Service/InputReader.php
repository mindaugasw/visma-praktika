<?php

namespace App\Service;

use App\Entity\HyphenationPattern;
use App\DataStructure\Trie\Trie;
use App\Entity\WordInput;
use App\Repository\HyphenationPatternRepository;
use App\Service\PsrLogger\LoggerInterface;
use Exception;
use SplFileObject;

class InputReader
{
    const PATTERNS_FILE = __DIR__.'/../../data/text-hyphenation-patterns.txt';
    const WORDS_FILE = __DIR__.'/../../data/test-dictionary-140k.txt';
    
    private ArgsHandler $argsHandler;
    private LoggerInterface $logger;
    private HyphenationPatternRepository $patternRepo;
    
    private ?array $patternList = null;
    private ?Trie $patternTree = null;
    
    public function __construct(ArgsHandler $argsHandler, LoggerInterface $logger, HyphenationPatternRepository $patternRepo)
    {
        $this->argsHandler = $argsHandler;
        $this->logger = $logger;
        $this->patternRepo = $patternRepo;
    }
    
    /**
     * Get patterns as array
     * @param bool $useDb Whether to read patterns from DB or from file
     * @param string $filePath
     * @return array<HyphenationPattern>
     */
	public function getPatternArray(bool $useDb = true, string $filePath = self::PATTERNS_FILE): array
	{
	    if ($this->patternList !== null)
	        return $this->patternList;
	    
	    if ($useDb) {
	        $this->patternList = $this->patternRepo->getAll();
        } else {
            $patterns = [];
            $this->readPatternsFile(
                $filePath,
                function (string $line) use (&$patterns)
                {
                    $patterns[] = new HyphenationPattern($line);
                }
            );
        
            $this->patternList = $patterns;
        }
        return $this->patternList;
    }
    
    /**
     * Get patterns as tree
     * @param bool $useDb
     * @param string $filePath
     * @return Trie
     */
	public function getPatternTree(bool $useDb = true, string $filePath = self::PATTERNS_FILE): Trie
    {
        if ($this->patternTree !== null)
            return $this->patternTree;
    
        $array = $this->getPatternArray($useDb, $filePath);
        
        Profiler::start("tree build");
        $tree = new Trie();
    
        foreach ($array as $p) {
            $tree->addValue($p->getPatternNoNumbers(), $p);
        }
        
        /*$this->readPatternsFile($filePath, function (string $line) use ($tree) {
            $pattern = new HyphenationPattern($line);
            $tree->addValue($pattern->getPatternNoNumbers(), $pattern);
        });*/
        $this->logger->debug('Tree built in %f ms', [Profiler::stop("tree build")]);
        
        $this->patternTree = $tree;
        return $tree;
    }
    
    /**
     * Get patterns array and tree, out which one will always be null, which
     * allows to pass both of them to the algorithm.
     * Chooses method by provided argument or $defaultMethod.
     * @param string $defaultMethod
     * @param bool $useDb If true, will read patterns from DB. If False, will read from default file.
     * @return array [array, tree]
     */
    public function getPatternMatchers(string $defaultMethod, bool $useDb = true): array
    {
        if ($this->argsHandler->get('method', $defaultMethod) === 'tree') {
            $array = null;
            $tree = $this->getPatternTree($useDb);
        } else {
            $array = $this->getPatternArray($useDb);
            $tree = null;
        }
        
        return [$array, $tree];
    }
    
    /**
     * Clear cached pattern array and tree to force rebuild them on next getPattern* call
     */
    public function clearPatternsCache(): void
    {
        $this->patternList = null;
        $this->patternTree = null;
    }
    
    /**
     * Read pattern file with callback for each pattern
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
	 * @param string $filePath
	 * @return array<WordInput>
	 */
	public function getWordList(string $filePath = self::WORDS_FILE): array
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