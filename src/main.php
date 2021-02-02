<?php
require_once("Entity/HyphenationPattern.php");
require_once("Entity/WordInput.php");
require_once("Entity/WordResult.php");
require_once("Service/InputReader.php");
require_once("Service/SyllablesAlgorithm.php");

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
