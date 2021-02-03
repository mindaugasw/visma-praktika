<?php

namespace Service;

use Entity\WordResult;

class OutputWriter
{
	public function printOneWordResultToConsole(WordResult $res)
	{
		echo $res->inputWithSpaces."\n";
		
		for ($i = 0; $i < count($res->numberMatrix); $i++)
		{
			$numbersRow = ""; // build string of a single row from numberMatrix
			for ($j = 0; $j < strlen($res->input); $j++)
			{
				$numbersRow .= " ".($res->numberMatrix[$i][$j] === -1 ? " " : $res->numberMatrix[$i][$j]);
			}
			echo "$numbersRow  ".$res->matchedPatterns[$i]->pattern."\n";
		}
		
		echo $res->resultWithNumbers."\n"
			.$res->resultWithSpaces."\n"
			.$res->result."\n"
			."Time taken: ".$res->time." ms\n\n";
	}
	
	public function writeBatchOutputToFile(array $words, string $outputFilePath)
	{
		
	}
}