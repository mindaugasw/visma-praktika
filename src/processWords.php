<?php
require_once('patternsList.php');
require_once('syllablesAlgorithm.php');

$patterns = getPatternsList();
$args = getopt("", ["input::", "batch::"]);

// Batch processing
if (isset($args["batch"]))
{
	echo "Batch processing - ".$args["batch"]."\n";
	processBatch(readBatchInput($args["batch"]), $patterns);
	return;
}

// Initial cli input
$firstInput = null;
if (isset($args["input"]))
{
	$firstInput = $args["input"];
}

// One word at a time processing
while (true)
{
	$input = "";
	if ($firstInput)
	{
		$input = $firstInput;
		$firstInput = null;
		echo "Enter word (or q to quit): $input\n";
	}
	else
		$input = readline("Enter word (or q to quit): ");
	
	if ($input === "q")
		break;
	
	processOneWord($input, $patterns);
	
}

function processOneWord(string $word, array $patterns)
{
	$res = wordToSyllables($word, $patterns);
	echo $res["originalWordWithSpaces"]."\n";
	
	$numberMatrix = $res["numberMatrix"];
	for ($i = 0; $i < count($numberMatrix); $i++)
	{
		$numbersRow = ''; // build string of a single row from numberMatrix
		for ($j = 0; $j < strlen($word); $j++)
		{
			$numbersRow .= " ".($numberMatrix[$i][$j] === -1 ? " " : $numberMatrix[$i][$j]);
		}
		echo "$numbersRow  ".$res["matchedPatterns"][$i]["pattern"]."\n";
	}
	
	echo $res["combinedWord"]."\n";
	echo $res["syllablesWord"]."\n";
	echo $res["syllablesWordShort"]."\n";
	echo "Time taken: ".$res["time"]." ms\n\n";
}

function processBatch(array $words, array $patterns)
{
	$count = count($words);
	echo "$count inputs\nItems done, time taken, +total correct -total incorrect\n";
	$good = 0;
	$bad = 0;
	
	$startTime = hrtime(true);
	for ($i = 0; $i < $count; $i++)
	{
		if ($i % 1000 === 0)
		{
			$endTime = hrtime(true);
			$totalTime = ($endTime - $startTime) / 1_000_000_000;
			
			echo "$i, took $totalTime s, +$good -$bad\n";
			
			$startTime = hrtime(true);
		}
		$word = $words[$i];
		$res = wordToSyllables($word[0], $patterns);
		if ($res["syllablesWordShort"] === $word[1])
			$good++;
		else
			$bad++;
	}
	
	echo "$good;$bad\n";
}

function readBatchInput(string $path): array
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
}