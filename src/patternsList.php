<?php

/**
 * Read and process patterns file
 * @return array
 */
function getPatternsList(): array
{
	$path = __DIR__."/../data/text-hyphenation-patterns.txt";
	$file = new SplFileObject($path);
	
	$patterns = [];
	
	while (!$file->eof())
	{
		$line = trim($file->fgets());
		$patternNoDots = preg_replace('/\./', '', $line);
		$text = preg_replace('/[\.\d]/', '', $line);
		
		$patterns[] = [							// Example values:
			"pattern" => $line,					// .mis1
			"patternNoDot" => $patternNoDots,	// mis1
			"text" => $text,					// mis
			"isStartPattern" => substr($line, 0, 1) === '.',	// true
			"isEndPattern"=> substr($line, -1) === '.',			// false
		];
	}
	
	return $patterns;
}