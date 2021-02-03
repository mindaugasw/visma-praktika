<?php

namespace Service;

use Entity\HyphenationPattern;
use Entity\WordInput;
use Entity\WordResult;
use Exception;
use SplFileObject;

class SyllablesAlgorithm
{
	private const TIME_DIVISOR_MS = 1_000_000; // ns to ms
	private const TIME_DIVISOR_S = 1_000_000_000; // ns to s
	private const ITEMS_IN_ONE_BATCH = 100;
	
	public function processOneWord(WordInput $input, array $patterns)
	{
		return $this->wordToSyllables($input, $patterns);
		//$this->printOneWordResult($res);
	}
	
	/**
	 * @param array<WordInput> $words
	 * @param array<HyphenationPattern> $patterns
	 * @param string $outputFilePath
	 */
	public function processBatch(array $words, array $patterns, string $outputFilePath)
	{
		//$outputFile = new SplFileObject($outputFilePath, "w");
		//$outputFile->fwrite("input,expectedResult,actualResult,isCorrect\n");
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
			if ($i % self::ITEMS_IN_ONE_BATCH === 0 && $i !== 0)
			{
				$endTime = hrtime(true);
				$totalTime = ($endTime - $startTime) / self::TIME_DIVISOR_S;
				
				echo "$i/$count, took $totalTime s, +$good -$bad\n";
				$good = 0;
				$bad = 0;
				
				$startTime = hrtime(true);
			}
			
			$res = $this->wordToSyllables($words[$i], $patterns);
			
			if ($res->isCorrect)
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
		$startTime = hrtime(true);
		
		$inputStr = $inputObj->input;
		$res = new WordResult($inputObj);
		
		for ($i = 0; $i < count($patterns); $i++)
		{
			/** @var HyphenationPattern $p Current pattern */
			$p = clone $patterns[$i];
			$pos = strpos($inputStr, $p->patternText);
			
			if ($pos !== false)
			{
				// ensure that start/end pattern positions are valid
				if ((!$p->isStartPattern || $pos === 0) &&
					(!$p->isEndPattern || $pos + strlen($p->patternText) === strlen($inputStr)) )
				{
					$p->position = $pos;
					$res->matchedPatterns[] = $p;
					
					// map pattern numbers to specific positions in word
					$res->numberMatrix[] = array_fill(0, strlen($inputStr), -1);
					$numberMatches = []; // numbers from this $p pattern, [[number, position in pattern]]
					preg_match_all('/\d/', $p->patternNoDot, $numberMatches, PREG_OFFSET_CAPTURE);
					$numberMatches = $numberMatches[0]; // remove extra nesting
					
					for ($j = 0; $j < count($numberMatches); $j++)
					{
						$res->numberMatrix
							[count($res->numberMatrix) - 1] // get last pattern row
							[$p->position + $numberMatches[$j][1] - 1 - $j] // number position in word // -$j to offset positions taken by other numbers in this pattern
							= $numberMatches[$j][0];
					}
				}
			}
		}
		
		$res->resultWithNumbers = self::combineWordWithNumbers($inputStr, $res->numberMatrix);
		$res->resultWithSpaces = strval(preg_replace(['/[13579]/', '/[2468]/'], ['-', ' '], $res->resultWithNumbers)); // TODO strval remove
		$res->result = strval(preg_replace('/ /', '', $res->resultWithSpaces));
		if (isset($res->expectedResult))
			$res->isCorrect = $res->result === $res->expectedResult;
		
		$endTime = hrtime(true);
		$res->time = ($endTime - $startTime) / self::TIME_DIVISOR_MS;
		
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
	
	/*private function printOneWordResult(WordResult $res)
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
	}*/
	
	
}