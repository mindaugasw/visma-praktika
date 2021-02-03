<?php

namespace Entity;

class WordInput
{
	/** @var string Original word input, e.g. mistranslate */
	public string $input;
	
	/** @var string Original word with spaces, e.g. m i s t r a n s l a t e */
	public string $inputWithSpaces;
	
	/* @var string Expected result with which actual result will be compared */
	public string $expectedResult;
	
	public function __construct(string $input, string $expectedResult = null)
	{
		$this->input = $input;
		$this->inputWithSpaces = chunk_split($input, 1, ' ');
		
		if ($expectedResult !== null)
			$this->expectedResult = $expectedResult;
	}
	
}