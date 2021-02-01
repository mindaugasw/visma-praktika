<?php
require_once('patternsList.php');
require_once('wordToSyllables.php');

$patterns = getPatternsList();
$args = getopt("", ["input:"]);
$firstInput = count($args) ? $args["input"] : null;

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
	
	$res = wordToSyllables($input, $patterns);
	echo $res["originalWordWithSpaces"]."\n";
	
	$numberMatrix = $res["numberMatrix"];
	for ($i = 0; $i < count($numberMatrix); $i++)
	{
		$numbersLine = '';
		
		for ($j = 0; $j < strlen($input); $j++)
		{
			$numbersLine .= " ".($numberMatrix[$i][$j] === -1 ? " " : $numberMatrix[$i][$j]);
		}
		echo "$numbersLine  ".$res["matchedPatterns"][$i]["pattern"]."\n";
	}
	
	echo $res["combinedWord"]."\n";
	echo $res["syllablesWord"]."\n";
	//echo $res["syllablesWordNoSpaces"]."\n";
	echo "Time taken: ".$res["time"]." ms\n\n";
	
}
