<?php

namespace App\Entity;

class WordInput
{
	/** @var string Original word input, e.g. mistranslate */
	protected string $input;
	
	/** @var string Original word with spaces, e.g. m i s t r a n s l a t e */
    protected string $inputWithSpaces;
	
	/** @var string Original word with leading and trailing dots, e.g. .mistranslate. */
    protected string $inputWithDots;
	
	/* @var string Expected result with which actual result will be compared */
    protected string $expectedResult;
	
	public function __construct(string $input, string $expectedResult = null)
	{
		$this->input = $input;
		$this->inputWithSpaces = chunk_split($input, 1, ' ');
		$this->inputWithDots = sprintf('.%s.', $input);
		
		if ($expectedResult !== null)
			$this->expectedResult = $expectedResult;
	}
	
	/**
	 * Original word input, e.g. mistranslate
	 * @return string
	 */
	public function getInput(): string
	{
		return $this->input;
	}
		
	/**
	 * Original word with spaces, e.g. m i s t r a n s l a t e
	 * @return string
	 */
	public function getInputWithSpaces(): string
	{
		return $this->inputWithSpaces;
	}
    
    /**
     * Original word with leading and trailing dots, e.g. .mistranslate.
     * @return string
     */
	public function getInputWithDots(): string
    {
        return $this->inputWithDots;
    }
	
	/**
	 * Expected result with which actual result will be compared.
	 * Can be null.
	 * @return string|null
	 */
	public function getExpectedResult()
	{
		return isset($this->expectedResult) ? $this->expectedResult : null;
	}
	
}