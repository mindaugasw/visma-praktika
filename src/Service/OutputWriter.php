<?php

namespace App\Service;

use App\Entity\WordResult;
use App\Exception\NotImplementedException;

class OutputWriter
{
    /**
     * Prints to console WordResult with all properties:
     * input with spaces
     * number matrix + matched patterns
     * result with numbers
     * result with spaces
     * result
     * calculation time
     * @param WordResult $res
     */
	public function printFullWordResult(WordResult $res): void
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
    
    /**
     * Print WordResult with minimal info, for when word is retrieved from DB
     * @param WordResult $wordResult
     */
	public function printMinimalWordResult(WordResult $wordResult): void
    {
        $patternsString = '';
        foreach ($wordResult->getMatchedPatterns() as $pattern)
            $patternsString .= sprintf('%s @ %d, ', $pattern->getPattern(), $pattern->getPosition());
        $patternsString = substr($patternsString, 0, -2); // remove trailing comma and space
        
        echo sprintf(
            "Found %d patterns (%s)\n%s\n\n",
            count($wordResult->getMatchedPatterns()),
            $patternsString,
            $wordResult->getResult());
    }
}