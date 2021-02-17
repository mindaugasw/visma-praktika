<?php

namespace App\Service\Hyphenator;

use App\DataStructure\TextSearchInterface;
use App\Entity\HyphenationPattern;
use App\Entity\WordInput;
use App\Entity\WordResult;
use App\Service\Profiler;

class Hyphenator
{
	
    /**
     * Syllabize one word
     * @param WordInput $inputObj Word to syllabize
     * @param TextSearchInterface $textSearch Data structure for handling text search. Should be already initialized with all patterns
     * @return WordResult
     */
	public function wordToSyllables(WordInput $inputObj, TextSearchInterface $textSearch): WordResult
	{
		$timer = Profiler::start();
		$result = new WordResult($inputObj);
		
		// find all patterns
		$result->setMatchedPatterns(
		    $textSearch->findMatches($inputObj->getInput())
        );
        
		// add to number matrix
        foreach ($result->getMatchedPatterns() as $matchedPattern) {
            $result->addToNumberMatrix(
                $this->buildMatrixRow($inputObj->getInput(), $matchedPattern)
            );
		}
        
		$this->setResultValues($result);
		$result->setTime(Profiler::stop($timer));
		return $result;
	}
    
	// Main algorithm helper methods
    
	/**
	 * Builds single matrix row for single pattern.
	 * I.e. maps pattern numbers to specific positions in the word
	 * @param string $input
	 * @param HyphenationPattern $pattern
	 * @return array<int>
	 */
	private function buildMatrixRow(string $input, HyphenationPattern $pattern): array
	{
        // TODO algorithm bug. On word 'dark' (default text input), in number matrix assigns value to -1 index
		$matrixRow = array_fill(0, strlen($input), -1);
		
		$numberMatches = []; // extracted numbers from this $pattern, [[[number, position in pattern], [...], ]]
		preg_match_all('/\d/', $pattern->getPatternNoDot(), $numberMatches, PREG_OFFSET_CAPTURE);
		$numberMatches = $numberMatches[0]; // remove extra nesting
		
		// add numbers to correct places in the row
		for ($j = 0; $j < count($numberMatches); $j++)
		{
		    // TODO skip last index number? Would prevent hyphen-at-word-end bugs (e.g. dark-, in-)
			$matrixRow
				[$pattern->getPosition() + $numberMatches[$j][1] - 1 - $j] // number position in word // -$j to offset positions taken by other numbers in this pattern
				= $numberMatches[$j][0];
		}
		
		return $matrixRow;
	}
    
    /**
     * Sets result values to $res: result, resultWithNumbers, resultWithSpaces, isCorrect.
     * Mutates original object
     * @param WordResult $res
     * @return WordResult
     */
    private function setResultValues(WordResult $res): WordResult
    {
        $res->setResultWithNumbers(
            $this->combineWordWithNumbers($res->getInput(), $res->getNumberMatrix())
        );
    
        $res->setResultWithSpaces(strval(preg_replace(
            ['/[13579]/', '/[2468]/'],
            ['-', ' '],
            $res->getResultWithNumbers()
        ))); // TODO strval remove
    
        $res->setResult(str_replace(' ', '', $res->getResultWithSpaces()));
    
        return $res;
    }
	
	/**
	 * Combine word with max number from each column in $numberMatrix.
	 * e.g mistranslate => m2i s1t4r a2n2s3l2a4t e
	 * @param string $word Input string
	 * @param array $numberMatrix
	 * @return string
	 */
	private function combineWordWithNumbers(string $word, array $numberMatrix): string
	{
		$combined = "";
		
		for ($i = 0; $i < strlen($word); $i++)
		{
			$combined .= substr($word, $i, 1);
			
			// find max number
			$maxNumber = -1;
			for ($j = 0; $j < count($numberMatrix); $j++)
			{
				if ($numberMatrix[$j][$i] > $maxNumber)
					$maxNumber = $numberMatrix[$j][$i];
			}
			
			// concatenate number
			if ($maxNumber === -1)
				$maxNumber = " ";
			else
				$maxNumber = strval($maxNumber);
			
			$combined .= $maxNumber;
		}
		
		return $combined;
	}
	
}
