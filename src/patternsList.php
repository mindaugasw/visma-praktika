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
		$text = preg_replace('/[\.\d]/', '', $line);
		$patterns[] = [
			"pattern" => $line,
			"text" => $text,
			"isStartPattern" => substr($line, 0, 1) === '.',
			"isEndPattern"=> substr($line, -1) === '.',
		];
		
		/*if (substr($line, 0, 1) === '.')
			$startPatterns[] = $line;
		else
			$patterns[] = $line;*/
	}
	
	//return [$startPatterns, $patterns];
	return $patterns;
}