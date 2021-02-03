<?php
require_once(__DIR__."/../autoload.php");

use Service\InputReader;
use Service\SyllablesAlgorithm;

$inputReader = new InputReader();
$alg = new SyllablesAlgorithm();

$args = getopt("", ["input::", "batch::", "outputDir::"]);
$patterns = $inputReader->getPatternsList(__DIR__."/../data/text-hyphenation-patterns.txt");

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
		$word = $inputReader->getWordInput($args);
		if ($word === false)
			return;
		
		$alg->processOneWord($word, $patterns);
	}
}
