<?php

namespace Service;

use Entity\HyphenationPattern;
use Entity\WordInput;
use Exception;
use SplFileObject;

class InputReader
{
	public static function getPatternsList()
	{
		$path = __DIR__."/../../data/text-hyphenation-patterns.txt";
		$file = new SplFileObject($path);
		
		$patterns = [];
		
		while (!$file->eof())
		{
			$line = trim($file->fgets());
			$patterns[] = new HyphenationPattern($line);
		}
		
		return $patterns;
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
	public static function getWordInput(array &$args)
	{
		$promptText = "Enter a word (or q to quit): ";
		$input = null;
		
		if (isset($args["input"]))
		{
			$input = $args["input"];
			unset($args["input"]);
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
	 */
	public static function getWordList(string $filePath)
	{
		throw new Exception("Not implemented");
		
		/*function readBatchInput(string $path): array
		{
			$file = new SplFileObject($path);
			$words = [];
			
			while (!$file->eof())
			{
				$line = $file->fgets();
				$word = explode(',', $line);
				$word[1] = trim($word[1]);
				$words[] = $word;
			}
			
			return $words;
		}*/
	}
}