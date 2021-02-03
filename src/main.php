<?php
require_once(__DIR__."/../autoload.php");

use Service\InputReader;
use Service\SyllablesAlgorithm;

$args = getopt("", ["input::", "batch::"]);
$patterns = InputReader::getPatternsList();

if (isset($args["batch"]))
{
	throw new Exception("Not implemented");
	echo "Batch processing - ".$args["batch"]."\n";
	//processBatch(readBatchInput($args["batch"]), $patterns);
	return;
}
else
{
	while (true)
	{
		$word = InputReader::getWordInput($args);
		if ($word === false)
			return;
		
		SyllablesAlgorithm::processOneWord($word, $patterns);
	}
}
