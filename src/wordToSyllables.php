<?php

function wordToSyllables(string $word, array $patterns): array
{
	echo "wrd:$word, sp:".count($patterns)."\n";
	
	$matchedPatterns = [];
	
	for ($i = 0; $i < count($patterns); $i++)
	{
		
		//$ptrn = $startPatterns[$i];
		//$wordPart = substr($word, 0, strlen($ptrn));
		//$pos = 
		//$pos = substr($word, 0, strlen($startPatterns[$i]))
		
		//if ($pos !== false)
		$pos = strpos($word, $patterns[$i]["text"]);
		if ($pos !== false)
		{
			if (($patterns[$i]["isStartPattern"] !== true || $pos === 0) &&
				($patterns[$i]["isEndPattern"] !== true || $pos + strlen($patterns[$i]["text"]) === strlen($word)) )
			{
				$match = $patterns[$i];
				$match["pos"] = $pos;
				$matchedPatterns[] = $match;
			}
		}
	}
	
	return $matchedPatterns;
}