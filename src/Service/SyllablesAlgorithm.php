<?php

namespace Service;

use Entity\HyphenationPattern;
use Entity\WordInput;
use Entity\WordResult;
use Exception;

class SyllablesAlgorithm
{
	public static function processOneWord(WordInput $input, array $patterns)
	{
		$res = self::wordToSyllables($input->input, $patterns);
		self::printOneWordResult($res);
	}
	
	public static function processBatch()
	{
		throw new Exception("Not implemented");
		/*function processBatch(array $words, array $patterns)
		{
			$count = count($words);
			echo "$count inputs\nItems done, time taken, +total correct -total incorrect\n";
			$good = 0;
			$bad = 0;
			
			$startTime = hrtime(true);
			for ($i = 0; $i < $count; $i++)
			{
				if ($i % 1000 === 0)
				{
					$endTime = hrtime(true);
					$totalTime = ($endTime - $startTime) / 1_000_000_000;
					
					echo "$i, took $totalTime s, +$good -$bad\n";
					
					$startTime = hrtime(true);
				}
				$word = $words[$i];
				$res = wordToSyllables($word[0], $patterns);
				if ($res["syllablesWordShort"] === $word[1])
					$good++;
				else
					$bad++;
			}
			
			echo "$good;$bad\n";
		}*/		
	}
	
	/**
	 * Syllabize one word
	 * @param string $input Word to syllabize
	 * @param array<HyphenationPattern> $patterns
	 * @return WordResult
	 */
	private static function wordToSyllables(string $input, array $patterns): WordResult
	{
		$startTime = hrtime(true);
		
		$res = new WordResult($input);
		
		for ($i = 0; $i < count($patterns); $i++)
		{
			/** @var HyphenationPattern $p Current pattern */
			$p = clone $patterns[$i];
			$pos = strpos($input, $p->patternText);
			
			if ($pos !== false)
			{
				// ensure that start/end pattern positions are valid
				if ((!$p->isStartPattern || $pos === 0) &&
					(!$p->isEndPattern || $pos + strlen($p->patternText) === strlen($input)) )
				{
					$p->position = $pos;
					$res->matchedPatterns[] = $p;
					
					// map pattern numbers to specific positions in word
					$res->numberMatrix[] = array_fill(0, strlen($input), -1);
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
		
		$res->resultWithNumbers = self::combineWordWithNumbers($input, $res->numberMatrix);
		$res->resultWithSpaces = strval(preg_replace(['/[13579]/', '/[2468]/'], ['-', ' '], $res->resultWithNumbers)); // TODO strval remove
		$res->result = strval(preg_replace('/ /', '', $res->resultWithSpaces));
		
		$endTime = hrtime(true);
		$res->time = ($endTime - $startTime) / 1_000_000;
		
		return $res;
	}
	
	/**
	 * Combine word with max number from each column in $numberMatrix.
	 * e.g mistranslate => m2i s1t4r a2n2s3l2a4t e
	 * @param string $word Input string
	 * @param array $numberMatrix
	 * @return string
	 */
	private static function combineWordWithNumbers(string $word, array $numberMatrix): string
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
	
	private static function printOneWordResult(WordResult $res)
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
}