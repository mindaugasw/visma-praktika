<?php

namespace Service;

use Entity\WordResult;

class OutputWriter
{
	public function printOneWordResultToConsole(WordResult $res): void
	{
		echo $res->getInputWithSpaces()."\n";
		
		for ($i = 0; $i < count($res->getNumberMatrix()); $i++)
		{
			$numbersRow = ""; // build string of a single row from numberMatrix
			for ($j = 0; $j < strlen($res->getInput()); $j++)
			{
				$numbersRow .= " ".($res->getNumberMatrix()[$i][$j] === -1 ? " " : $res->getNumberMatrix()[$i][$j]);
			}
			echo "$numbersRow  ".$res->getMatchedPatterns()[$i]->getPattern()."\n";
		}
		
		echo $res->getResultWithNumbers()."\n"
			.$res->getResultWithSpaces()."\n"
			.$res->getResult()."\n"
			."Time taken: ".$res->getTime()." ms\n\n";
	}
	
	public function writeBatchOutputToFile(array $words, string $outputFilePath)
	{
		// TODO
		//$outputFile = new SplFileObject($outputFilePath, "w");
		//$outputFile->fwrite("input,expectedResult,actualResult,isCorrect\n");
		
	}
}