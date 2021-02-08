<?php

namespace App\Service;

use App\Entity\WordResult;
use App\Exception\NotImplementedException;

class OutputWriter
{
	public function printOneWordResultToConsole(WordResult $res): void
	{
		echo $res->getInputWithSpaces()."\n";
		
		for ($i = 0; $i < count($res->getNumberMatrix()); $i++)
		{
			$numbersRow = ''; // build string of a single row from numberMatrix
			for ($j = 0; $j < strlen($res->getInput()); $j++)
			{
				$numbersRow .= " ".($res->getNumberMatrix()[$i][$j] === -1 ? " " : $res->getNumberMatrix()[$i][$j]);
			}
			echo sprintf("%s %s\n", $numbersRow, $res->getMatchedPatterns()[$i]->getPattern());
		}
		
		echo sprintf(
                "%s\n%s\n%s\nTime taken: %f ms\n\n",
                $res->getResultWithNumbers(),
                $res->getResultWithSpaces(),
                $res->getResult(),
                $res->getTime()
            );
	}
	
	public function writeBatchOutputToFile(array $words, string $outputFilePath)
	{
	    throw new NotImplementedException();
		//$outputFile = new SplFileObject($outputFilePath, "w");
		//$outputFile->fwrite("input,expectedResult,actualResult,isCorrect\n");
		
	}
}