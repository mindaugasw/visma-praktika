<?php
require_once(__DIR__."/../autoload.php");

use App\Service\InputReader;
use App\Service\OutputWriter;
use App\Service\Profiler;
use App\Service\SyllablesAlgorithm;

$reader = new InputReader();
$writer = new OutputWriter();
$alg = new SyllablesAlgorithm();

$patternsFile = __DIR__."/../data/text-hyphenation-patterns.txt";

$args = getopt("", [
    InputReader::ARGS_SINGLE_INPUT."::",
    InputReader::ARGS_BATCH_INPUT."::",
    InputReader::ARGS_BATCH_OUTPUT."::",
    InputReader::ARGS_SEARCH_METHOD."::"
]);

$method = $reader->getSearchMethod($args);
echo "Using $method method.\n";
$patternsArray = null;
$patternsTrie = null;
if ($method === InputReader::ARGS_SEARCH_METHOD_ARRAY)
    $patternsArray = $reader->getPatternList($patternsFile);
else {
    Profiler::start("Trie build");
    $patternsTrie = $reader->getPatternTrie($patternsFile);
    Profiler::stopEcho("Trie build");
}


if (isset($args[InputReader::ARGS_BATCH_INPUT]))
{
	/*if (!file_exists($args[InputReader::ARGS_BATCH_INPUT]))
		throw new Exception("Input file not found");
	if (!isset($args[InputReader::ARGS_BATCH_OUTPUT]))
		throw new Exception("Output file argument missing");
	
	
	$words = $reader->getWordList($args[InputReader::ARGS_BATCH_INPUT]);
	$alg->processBatch($words, $patterns, $args[InputReader::ARGS_BATCH_OUTPUT]);
	
	return;*/
} else {
	while (true)
	{
		$word = $reader->getSingleWordInput($args);
		if ($word === false)
			return;
		
		$res = $alg->processOneWord($word, $patternsArray, $patternsTrie);
		$writer->printOneWordResultToConsole($res);
	}
}
