<?php
require_once(__DIR__."/../autoload.php");

use App\Service\InputReader;
use App\Service\OutputWriter;
use App\Service\SyllablesAlgorithm;

$reader = new InputReader();
$writer = new OutputWriter();
$alg = new SyllablesAlgorithm();

$args = getopt("", [InputReader::ARGS_SINGLE_INPUT."::", InputReader::ARGS_BATCH_INPUT."::", InputReader::ARGS_BATCH_OUTPUT."::"]);
$patterns = $reader->getPatternList(__DIR__."/../data/text-hyphenation-patterns.txt");
$patternTree = $reader->getPatternTree(__DIR__."/../data/text-hyphenation-patterns.txt");

die();
if (isset($args[InputReader::ARGS_BATCH_INPUT]))
{
	if (!file_exists($args[InputReader::ARGS_BATCH_INPUT]))
		throw new Exception("Input file not found");
	if (!isset($args[InputReader::ARGS_BATCH_OUTPUT]))
		throw new Exception("Output file argument missing");
	
	
	$words = $reader->getWordList($args[InputReader::ARGS_BATCH_INPUT]);
	$alg->processBatch($words, $patterns, $args[InputReader::ARGS_BATCH_OUTPUT]);
	
	return;
}
else
{
	while (true)
	{
		$word = $reader->getSingleWordInput($args);
		if ($word === false)
			return;
		
		$res = $alg->processOneWord($word, $patterns);
		$writer->printOneWordResultToConsole($res);
	}
}
