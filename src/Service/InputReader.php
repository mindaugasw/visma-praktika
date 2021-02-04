<?php

namespace App\Service;

use App\Entity\HyphenationPattern;
use App\Entity\Trie\Trie;
use App\Entity\WordInput;
use Exception;
use SplFileObject;

class InputReader
{
	const ARGS_SINGLE_INPUT = "input";
	const ARGS_BATCH_INPUT = "batch";
	const ARGS_BATCH_OUTPUT = "batchOutput";
	
	public function getPatternList(string $path)
	{
		$patterns = [];
		
        $this->readPatternsFile($path, function (string $line) use (&$patterns) {
            $patterns[] = new HyphenationPattern($line);
        });
		
		return $patterns;
	}
	
	public function getPatternTree(string $path): Trie
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
            //$patterns[] = new HyphenationPattern($line);
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