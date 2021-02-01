<?php

function wordToSyllables(string $word, array $patterns): array
{
	$startTime = hrtime(true);
	
	$matchedPatterns = [];
	$numberMatrix = [];
	
	for ($i = 0; $i < count($patterns); $i++)
	{
		$pos = strpos($word, $patterns[$i]["text"]);
		if ($pos !== false)
		{
			if (($patterns[$i]["isStartPattern"] !== true || $pos === 0) &&
				($patterns[$i]["isEndPattern"] !== true || $pos + strlen($patterns[$i]["text"]) === strlen($word)) )
			{
				$match = $patterns[$i];
				$match["pos"] = $pos;
				$matchedPatterns[] = $match;
				
				$numberMatrix[] = array_fill(0, strlen($word), -1);
				
				preg_match_all('/\d/', $match["patternNoDot"], $numberMatches, PREG_OFFSET_CAPTURE);
				for ($j = 0; $j < count($numberMatches[0]); $j++)
				{
					$numberMatches[0][$j][1] = $numberMatches[0][$j][1] - $j; // Fix number index if there's more than 1 number in a pattern
					$numberMatrix[count($numberMatrix) - 1][$match["pos"] + $numberMatches[0][$j][1] - 1] = $numberMatches[0][$j][0];
				}
			}
		}
	}
	
	$originalWordWithSpaces = chunk_split($word, 1, ' ');
	$combinedWord = combineNumbers($word, $numberMatrix);
	$syllablesWord = preg_replace(['/[13579]/', '/[2468]/'], ['-', ' '], $combinedWord);
	$syllablesWordNoSpaces = preg_replace('/ /', '', $syllablesWord);
	
	$endTime = hrtime(true);
	$totalTime = ($endTime - $startTime) / 1_000_000;
	
	return [
		"originalWordWithSpaces" => $originalWordWithSpaces,
		"combinedWord" => $combinedWord,
		"syllablesWord" => $syllablesWord,
		"syllablesWordNoSpaces" => $syllablesWordNoSpaces,
		"matchedPatterns" => $matchedPatterns,
		"numberMatrix" => $numberMatrix,
		"time" => $totalTime];
}

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
		
		if ($maxNumber === -1)
			$maxNumber = " ";
		else
			$maxNumber = strval($maxNumber);
		
		$combined .= $maxNumber;
		
	}
	
	return $combined;
}