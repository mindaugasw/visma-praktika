<?php

namespace App\Service;

use App\Entity\HyphenationPattern;
use App\Entity\Trie\Trie;
use App\Entity\WordInput;
use App\Exception\NotImplementedException;
use Exception;
use SplFileObject;

class InputReader
{
    // cli argument keys/values
	const ARGS_SINGLE_INPUT = "input";
	const ARGS_BATCH_INPUT = "batch";
	const ARGS_BATCH_OUTPUT = "batchOutput";
	const ARGS_SEARCH_METHOD = "method";
	const ARGS_SEARCH_METHOD_ARRAY = "array";
	const ARGS_SEARCH_METHOD_TREE = "tree";
    
	
    /**
     * Determine search method to use.
     * User-defined method has highest priority.
     * Otherwise chooses array for one word and tree for batch processing.
     * @param array $args
     * @return string
     * @throws Exception
     */
    public function getSearchMethod(array $args): string
    {
        // user-defined method has highest priority
        if (isset($args[self::ARGS_SEARCH_METHOD])) {
            $method = $args[self::ARGS_SEARCH_METHOD];
            if ($method !== self::ARGS_SEARCH_METHOD_ARRAY && $method !== self::ARGS_SEARCH_METHOD_TREE)
                throw new Exception("Unknown search method \"$method\"");
            else
                return $method;
        }
        
        // for batch processing use tree - long build time, quick each word processing
        if (isset($args[self::ARGS_BATCH_INPUT]))
            return self::ARGS_SEARCH_METHOD_TREE;
        
        // for single word use array - longer each word processing
        //if (isset($args[self::ARGS_SINGLE_INPUT])) // default value
        return self::ARGS_SEARCH_METHOD_ARRAY;
    }
	
	public function getPatternList(string $path)
	{
		$patterns = [];
		
        $this->readPatternsFile($path, function (string $line) use (&$patterns) {
            $patterns[] = new HyphenationPattern($line);
        });
		
		return $patterns;
	}
	
	public function getPatternTrie(string $path): Trie
    {
        $tree = new Trie();
        
        $this->readPatternsFile($path, function (string $line) use ($tree) {
            $pattern = new HyphenationPattern($line);
            $tree->addValue($pattern->getPatternNoNumbers(), $pattern);
        });
        
        return $tree;
    }
    
    private function readPatternsFile(string $path, $callback): void
    {
        $file = new SplFileObject($path);
    
        while (!$file->eof())
        {
            $line = trim($file->fgets());
            $callback($line);
        }
    }
	
	/**
	 * Get single word for processing.
	 * If $args["input"] is set, uses it's value and unsets it.
	 * Otherwise prompts user for input.
	 * Returns input string or false if "q" was entered.
	 * 
	 * @param array $args
	 * @return WordInput|bool
	 */
	public function getSingleWordInput(array &$args)
	{
		$promptText = "Enter a word (or q to quit): ";
		$input = null;
		
		if (isset($args[self::ARGS_SINGLE_INPUT]))
		{
			$input = $args[self::ARGS_SINGLE_INPUT];
			unset($args[self::ARGS_SINGLE_INPUT]);
			echo "$promptText$input\n";
		}
		else
		{
			$input = readline($promptText);
		}
		
		if ($input === "q")
			return false;
		else
			return new WordInput($input);
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