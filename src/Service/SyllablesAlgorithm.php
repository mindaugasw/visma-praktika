<?php

namespace App\Service;

use App\Entity\HyphenationPattern;
use App\Entity\WordInput;
use App\Entity\WordResult;
use Exception;
use SplFileObject;

class SyllablesAlgorithm
{
	private const ITEMS_IN_ONE_BATCH = 100;
	
	/**
	 * @param WordInput $input
	 * @param array<HyphenationPattern> $patterns
	 * @return WordResult
	 */
	public function processOneWord(WordInput $input, array $patterns): WordResult
	{
		return $this->wordToSyllables($input, $patterns);
	}
	
	/**
	 * @param array<WordInput> $words
	 * @param array<HyphenationPattern> $patterns
	 * @param string $outputFilePath
	 */
	public function processBatch(array $words, array $patterns, string $outputFilePath)
	{
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
	
	/**
	 * Syllabize one word
	 * @param WordInput $inputObj Word to syllabize
	 * @param array<HyphenationPattern> $patterns
	 * @return WordResult
	 */
	private function wordToSyllables(WordInput $inputObj, array $patterns): WordResult
	{
		$timer = Profiler::start();
		
		$inputStr = $inputObj->getInput();
		$res = new WordResult($inputObj);
		
		for ($i = 0; $i < count($patterns); $i++)
		{
			/** @var HyphenationPattern $p Current pattern */
			$p = clone $patterns[$i];
			$pos = $this->findPatternInWord($inputStr, $p);
			
			if ($pos !== -1)
			{
				$p->setPosition($pos);
				
				$res->addMatchedPattern($p);
				
				$res->addToNumberMatrix($this->buildMatrixRow($inputStr, $p));
				
				
				/*// map pattern numbers to specific positions in word
				$numberMatrix = &$res->getNumberMatrix();
				//$res->getNumberMatrix()[] = array_fill(0, strlen($inputStr), -1); // Doesn't work as array is returned by value
				$numberMatrix[] = array_fill(0, strlen($inputStr), -1);
				
				$numberMatches = []; // numbers from this $p pattern, [[[number, position in pattern], [...], ]]
				preg_match_all('/\d/', $p->getPatternNoDot(), $numberMatches, PREG_OFFSET_CAPTURE);
				$numberMatches = $numberMatches[0]; // remove extra nesting
				
				for ($j = 0; $j < count($numberMatches); $j++)
				{
					$numberMatrix
						[count($numberMatrix) - 1] // get last pattern row
						[$p->getPosition() + $numberMatches[$j][1] - 1 - $j] // number position in word // -$j to offset positions taken by other numbers in this pattern
						= $numberMatches[$j][0];
				}*/
			}
		}
		
		$res->setResultWithNumbers(
			$this->combineWordWithNumbers($inputStr, $res->getNumberMatrix())
		);
		
		$res->setResultWithSpaces(strval(preg_replace(
			['/[13579]/', '/[2468]/'],
			['-', ' '],
			$res->getResultWithNumbers()
		))); // TODO strval remove
		
		$res->setResult(str_replace(' ', '', $res->getResultWithSpaces()));
		
		if ($res->getExpectedResult() !== null)
			$res->setIsCorrect($res->getResult() === $res->getExpectedResult());
		
		$endTime = hrtime(true);
		$res->setTime(Profiler::stop($timer));
		
		return $res;
	}
	
	// Main algorithm helper methods
	
	/**
	 * Find if this $pattern exists in $input and return its position or -1 
	 * @param string $input
	 * @param HyphenationPattern $pattern
	 * @return int Pattern position in $input or -1 if not found
	 */
	private function findPatternInWord(string $input, HyphenationPattern $pattern): int
	{
		$pos = strpos($input, $pattern->getPatternText());
		
		// pattern not found
		if ($pos === false)
			return -1;
		
		// start pattern isn't at the start
		if ($pattern->isStartPattern() && $pos !== 0)
			return -1;
		
		// end pattern isn't at the end
		if ($pattern->isEndPattern() && $pos + strlen($pattern->getPatternText()) !== strlen($input))
			return -1;
		
		return $pos;
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
		$matrixRow = array_fill(0, strlen($input), -1);
		
		$numberMatches = []; // extracted numbers from this $pattern, [[[number, position in pattern], [...], ]]
		preg_match_all('/\d/', $pattern->getPatternNoDot(), $numberMatches, PREG_OFFSET_CAPTURE);
		$numberMatches = $numberMatches[0]; // remove extra nesting
		
		// add numbers to correct places in the row
		for ($j = 0; $j < count($numberMatches); $j++)
		{
			$matrixRow
				[$pattern->getPosition() + $numberMatches[$j][1] - 1 - $j] // number position in word // -$j to offset positions taken by other numbers in this pattern
				= $numberMatches[$j][0];
		}
		
		return $matrixRow;
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