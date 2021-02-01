<?php

function getPatternsList(): array
{
	$path = __DIR__."/../data/text-hyphenation-patterns.txt";
	//echo "PATH:".$path."\n";
	//echo "EXISTS:".json_encode(file_exists($path))."\n";
	$file = new SplFileObject($path);
	
	//$startPatterns = [];
	$patterns = [];
	
	//while ($line = $file->fgets() !== false)
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
		
		/*if (substr($line, 0, 1) === '.')
			$startPatterns[] = $line;
		else
			$patterns[] = $line;*/
	}
	
	//return [$startPatterns, $patterns];
	return $patterns;
}