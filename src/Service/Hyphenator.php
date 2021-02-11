<?php

namespace App\Service;

use App\Entity\HyphenationPattern;
use App\DataStructure\Trie\Trie;
use App\Entity\WordInput;
use App\Entity\WordResult;
use App\Exception\NotImplementedException;
use Exception;
use SplFileObject;

class Hyphenator
{
	//private const ITEMS_IN_ONE_BATCH = 100;
	
    /**
     * Syllabize one word
     * @param WordInput $inputObj Word to syllabize
     * @param ?array<HyphenationPattern> $patternsArray Pattern array. Can be null if $patternsTree is provided
     * @param ?Trie $patternsTree Tree for patterns search. Can be null if $patternsArray ir provided
     * @return WordResult
     */
	public function wordToSyllables(WordInput $inputObj, ?array $patternsArray, ?Trie $patternsTree = null): WordResult
	{
		$timer = Profiler::start();
		$result = new WordResult($inputObj);
		
		if ($patternsArray !== null) { // Use pattern array for search
		    foreach ($patternsArray as $patternOriginal) {
                /** @var HyphenationPattern $pattern Current pattern */
                $pattern = clone $patternOriginal; // clone to set position specifically for this word
                // TODO doesn't work if same pattern is multiple times in the word: only 1st occurrence is returned
                $pos = $this->findPatternInWord($inputObj->getInput(), $pattern);
                
                if ($pos !== -1) {
                    $pattern->setPosition($pos);
                    $result->addMatchedPattern($pattern);
                    $result->addToNumberMatrix(
                        $this->buildMatrixRow($inputObj->getInput(), $pattern)
                    );
                }
            }
            
        } else if ($patternsTree !== null) { // use pattern tree for search
		    
		    $result->setMatchedPatterns($patternsTree->findMatches($inputObj->getInputWithDots()));
		    
		    foreach ($result->getMatchedPatterns() as $pattern) {
		        $result->addToNumberMatrix(
		            $this->buildMatrixRow($inputObj->getInput(), $pattern)
                );
            }
		    
        } else
            throw new Exception('No method selected for pattern search');
		
		$this->setResultValues($result);
		$result->setTime(Profiler::stop($timer));
		return $result;
	}
    
	// Main algorithm helper methods
    
	/**
	 * Find if this $pattern exists in $input and return its position or -1 
     * Causes 30-50% #performance drop comparing with inlining
	 * @param string $input
	 * @param HyphenationPattern $pattern
	 * @return int Pattern position in $input or -1 if not found
	 */
	private function findPatternInWord(string $input, HyphenationPattern $pattern): int
	{
		$position = strpos($input, $pattern->getPatternText());
		
		// pattern not found
		if ($position === false)
			return -1;
		
		// start pattern isn't at the start
		if ($pattern->isStartPattern() && $position !== 0)
			return -1;
		
		// end pattern isn't at the end
		if ($pattern->isEndPattern() && $position + strlen($pattern->getPatternText()) !== strlen($input))
			return -1;
		
		return $position;
	}
	
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
	
	// Unmaintained
    
    /**
     * @param array<WordInput> $words
     * @param array<HyphenationPattern> $patterns
     * @param string $outputFilePath
     */
    public function processBatch(array $words, array $patterns, string $outputFilePath)
    {
        throw new NotImplementedException();
        
        /** @var array<WordResult> $outputWords */
        $outputWords = [];
        /** @var array<WordResult> WordResult $badWords */
        $badWords = [];
        
        $count = count($words);
        
        echo "Batch processing $count words, output in $outputFilePath\n\n"
            ."Items done, time taken, +correct -incorrect\n";;
        
        $good = 0;
        $bad = 0;
        $startTime = hrtime(true);
        
        for ($i = 0; $i < $count; $i++)
        {
            // Pause processing to print out intermediate results
            if ($i % self::ITEMS_IN_ONE_BATCH === 0 && $i !== 0)
            {
                $endTime = hrtime(true);
                $totalTime = -1;// ($endTime - $startTime) / self::TIME_DIVISOR_S; // TODO fix using Profiler
                
                echo "$i/$count, took $totalTime s, +$good -$bad\n";
                $good = 0;
                $bad = 0;
                
                $startTime = hrtime(true);
            }
            
            $res = $this->wordToSyllables($words[$i], $patterns);
            
            if ($res->isCorrect())
                $good++;
            else
            {
                $bad++;
                $badWords[] = $res;
            }
            $outputWords[] = $res;
        }
        
        echo "\nCompleted. Bad words (".count($badWords).", shown up to 30):\nInput, Expected, Actual:\n";
        for ($i = 0; $i < min(count($badWords), 30); $i++)
        {
            echo $badWords[$i]->input.", "
                .$badWords[$i]->expectedResult.", "
                .$badWords[$i]->result."\n";
        }
    }
    
    
}
