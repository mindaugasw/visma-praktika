<?php

namespace Entity;

class WordResult extends WordInput
{
	/** @var string Final result - word divided into syllables, e.g. mis-trans-late */
	public string $result;
	
	/** @var string Result with spaces, e.g. m i s-t r a n s-l a t e */
	public string $resultWithSpaces;
	
	/** @var string Result with numbers, e.g. m2i s1t4r a2n2s3l2a4t e */
	public string $resultWithNumbers;
	
	/** @var array<HyphenationPattern> All patterns found in this word */
	public array $matchedPatterns;
	
	/** @var array<array<int>> Numbers from patterns placed in their respective positions in the word */
	public array $numberMatrix;
	
	/** @var float Processing time, ms */
	public float $time;
	
	
	public function __construct(string $input)
	{
		parent::__construct($input);
		$this->matchedPatterns = [];
		$this->numberMatrix = [];
	}
	
}