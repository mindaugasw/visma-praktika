<?php

/**
 * Split word into syllables.
 * 
 * Returns: [
 * 		originalWordWithSpaces =>	m i s t r a n s l a t e
 * 		combinedWord =>				m2i s1t4r a2n2s3l2a4t e
 * 		syllablesWord =>			m i s-t r a n s-l a t e
 * 		syllablesWordShort =>		mis-trans-late
 * 		matchedPatterns => [ [pattern, text, pos, ...], ]
 * 		numberMatrix => [ [-1, 2, -1, ...], ]
 * 		time => 18.582787 (ms)
 * ]
 * @param string $word
 * @param array $patterns
 * @return array
 */
function wordToSyllables(string $word, array $patterns): array
{
	$startTime = hrtime(true);
	
	$matchedPatterns = []; // matched pattern objects 
	$numberMatrix = []; // for each pattern (y), array (x) with pattern numbers in respective position
	
	for ($i = 0; $i < count($patterns); $i++)
	{
		$pos = strpos($word, $patterns[$i]["text"]);
		if ($pos !== false)
		{
			// ensure that start/end pattern positions are valid
			if (($patterns[$i]["isStartPattern"] !== true || $pos === 0) &&
				($patterns[$i]["isEndPattern"] !== true || $pos + strlen($patterns[$i]["text"]) === strlen($word)) )
			{
				$match = $patterns[$i];
				$match["pos"] = $pos;
				$matchedPatterns[] = $match;
				
				// map pattern numbers to specific positions in word
				$numberMatrix[] = array_fill(0, strlen($word), -1); 
				
				// $numberMatches - numbers from pattern, [[number, position in pattern]]
				preg_match_all('/\d/', $match["patternNoDot"], $numberMatches, PREG_OFFSET_CAPTURE);
				for ($j = 0; $j < count($numberMatches[0]); $j++)
				{
					$numberMatrix
						[count($numberMatrix) - 1] // get last pattern row
						[$match["pos"] + $numberMatches[0][$j][1] - 1 - $j] // number position in word // -$j to offset positions taken by other numbers in this pattern
						= $numberMatches[0][$j][0];
				}
			}
		}
	}
	
	$originalWordWithSpaces = chunk_split($word, 1, ' ');
	$combinedWord = combineNumbers($word, $numberMatrix);
	$syllablesWord = preg_replace(['/[13579]/', '/[2468]/'], ['-', ' '], $combinedWord);
	$syllablesWordShort = preg_replace('/ /', '', $syllablesWord);
	
	$endTime = hrtime(true);
	$totalTime = ($endTime - $startTime) / 1_000_000;
	
	return [
		"originalWordWithSpaces" => $originalWordWithSpaces,
		"combinedWord" => $combinedWord,
		"syllablesWord" => $syllablesWord,
		"syllablesWordShort" => $syllablesWordShort,
		"matchedPatterns" => $matchedPatterns,
		"numberMatrix" => $numberMatrix,
		"time" => $totalTime];
}

/**
 * Combine word with max number from each column in numberMatrix.
 * @param string $word
 * @param array $numberMatrix
 * @return string e.g. mistranslate => m2i s1t4r a2n2s3l2a4t e
 */
function combineNumbers(string $word, array $numberMatrix): string
{
	$combined = "";
	
	for ($i = 0; $i < strlen($word); $i++)
	{
		$combined .= substr($word, $i, 1);
		
		// find max number
		$maxNumber = -1;
		for ($j = 0; $j < count($numberMatrix); $j++)
		{
			$currentNumber = $numberMatrix[$j][$i]; 
			if ($currentNumber > $maxNumber)
				$maxNumber = $currentNumber;
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