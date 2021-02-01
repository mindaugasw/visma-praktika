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
	
	wordToSyllables($input, $patterns);
	
}
