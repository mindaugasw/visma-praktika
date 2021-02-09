<?php

namespace App\Service;

use App\Entity\HyphenationPattern;
use App\DataStructure\Trie\Trie;
use App\Entity\WordInput;
use Exception;
use SplFileObject;

class InputReader
{
    const PATTERNS_FILE = __DIR__.'/../../data/text-hyphenation-patterns.txt';
    
    private ArgsParser $argsParser;
    
    /*// cli args keys
    const ARGS_COMMAND = 'command';
    const ARGS_SINGLE_INPUT = 'input';
    const ARGS_FILE_INPUT = 'file';
    const ARGS_FILE_OUTPUT = 'output';
    const ARGS_METHOD = 'method';
    
    private array $argsConfig = [
        self::ARGS_COMMAND => [
            'long' => 'command',
            'short' => 'c',
        ],
        self::ARGS_SINGLE_INPUT => [ // console input for single word/text
            'long' => 'input',
            'short' => 'i',
        ],
        self::ARGS_FILE_INPUT => [ // file path
            'long' => 'file',
            'short' => 'f',
        ],
        //'batch' => [] // batch input
        self::ARGS_FILE_OUTPUT => [ // output file
            'long' => 'output',
            'short' => 'o',
        ],
        self::ARGS_METHOD => [ // pattern search method
            'long' => 'method',
            'short' => 'm',
            'values' => [
                'array',
                'tree',
            ],
        ],
    ];
    
	private array $args;*/
    private ?array $patternList = null;
    private ?Trie $patternTree = null;
    
    public function __construct(ArgsParser $argsParser)
    {
        $this->argsParser = $argsParser;
    }
	
    /**
     * Get patterns as array
     * @return array<HyphenationPattern>
     */
	public function getPatternArray(): array
	{
	    if ($this->patternList !== null)
	        return $this->patternList;
	    
		$patterns = [];
		
        $this->readPatternsFile(self::PATTERNS_FILE, function (string $line) use (&$patterns) {
            $patterns[] = new HyphenationPattern($line);
        });
		
        $this->patternList = $patterns;
		return $patterns;
	}
    
    /**
     * Get patterns as tree
     * @return Trie
     */
	public function getPatternTree(): Trie
    {
        if ($this->patternTree !== null)
            return $this->patternTree;
    
        Profiler::start("tree build");
        $tree = new Trie();
        
        $this->readPatternsFile(self::PATTERNS_FILE, function (string $line) use ($tree) {
            $pattern = new HyphenationPattern($line);
            $tree->addValue($pattern->getPatternNoNumbers(), $pattern);
        });
        Profiler::stopEcho("tree build");
        
        $this->patternTree = $tree;
        return $tree;
    }
    
    /**
     * Get patterns array and tree, out which one will always be null, which
     * allows to pass both of them to the algorithm.
     * Chooses method by provided argument or $defaultMethod.
     * @param string $defaultMethod
     * @return array [array, tree]
     */
    public function getPatternMatchers(string $defaultMethod): array
    {
        if ($this->argsParser->get('method', 'tree') === 'tree') {
            $array = null;
            $tree = $this->getPatternTree();
        } else {
            $array = $this->getPatternArray();
            $tree = null;
        }
        
        return [$array, $tree];
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
	public function getWordList(string $filePath): array
	{
		// TODO fix multibyte encoding when reading from file
		throw new Exception();
		
		$file = new SplFileObject($filePath);
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