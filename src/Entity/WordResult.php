<?php

namespace Entity;

class WordResult extends WordInput
{
	/** @var string Final result - word divided into syllables, e.g. mis-trans-late */
	private string $result;
	
	/** @var string Result with spaces, e.g. m i s-t r a n s-l a t e */
	private string $resultWithSpaces;
	
	/** @var string Result with numbers, e.g. m2i s1t4r a2n2s3l2a4t e */
	private string $resultWithNumbers;
	
	/** @var array<HyphenationPattern> All patterns found in this word */
	private array $matchedPatterns;
	
	/** @var array<array<int>> Numbers from patterns placed in their respective positions in the word */
	private array $numberMatrix;
	
	/** @var float Processing time, ms */
	private float $time;
	
	/** @var bool Does $result match $expectedResult? */
	private bool $isCorrect;
	
	
	public function __construct(WordInput $input)
	{
		parent::__construct($input->getInput(), $input->getExpectedResult());
		$this->matchedPatterns = [];
		$this->numberMatrix = [];
	}
	
	
	/**
	 * Final result - word divided into syllables, e.g. mis-trans-late
	 * @return string
	 */
	public function getResult(): string
	{
		return $this->result;
	}
	
	/**
	 * @param string $result
	 */
	public function setResult(string $result): void
	{
		$this->result = $result;
	}
	
	/**
	 * Result with spaces, e.g. m i s-t r a n s-l a t e
	 * @return string
	 */
	public function getResultWithSpaces(): string
	{
		return $this->resultWithSpaces;
	}
	
	/**
	 * @param string $resultWithSpaces
	 */
	public function setResultWithSpaces(string $resultWithSpaces): void
	{
		$this->resultWithSpaces = $resultWithSpaces;
	}
	
	/**
	 * Result with numbers, e.g. m2i s1t4r a2n2s3l2a4t e
	 * @return string
	 */
	public function getResultWithNumbers(): string
	{
		return $this->resultWithNumbers;
	}
	
	/**
	 * @param string $resultWithNumbers
	 */
	public function setResultWithNumbers(string $resultWithNumbers): void
	{
		$this->resultWithNumbers = $resultWithNumbers;
	}
	
	/**
	 * All patterns found in this word
	 * @return array<HyphenationPattern>
	 */
	public function getMatchedPatterns(): array
	{
		return $this->matchedPatterns;
	}
	
	/**
	 * @param HyphenationPattern $pattern
	 */
	public function addMatchedPattern(HyphenationPattern $pattern): void
	{
		$this->matchedPatterns[] = $pattern;
	}
	
	/**
	 * Numbers from patterns placed in their respective positions in the word
	 * @return array<array<int>>
	 */
	public function getNumberMatrix(): array
	{
		return $this->numberMatrix;
	}
	
	/**
	 * @param array<int> $matrixRow
	 */
	public function addToNumberMatrix(array $matrixRow): void
	{
		$this->numberMatrix[] = $matrixRow;
	}
	
	/**
	 * Processing time, ms
	 * @return float
	 */
	public function getTime(): float
	{
		return $this->time;
	}
	
	/**
	 * @param float $time
	 */
	public function setTime(float $time): void
	{
		$this->time = $time;
	}
	
	/**
	 * Does $result match $expectedResult?
	 * @return bool
	 */
	public function isCorrect(): bool
	{
		return $this->isCorrect;
	}
	
	/**
	 * @param bool $isCorrect
	 */
	public function setIsCorrect(bool $isCorrect): void
	{
		$this->isCorrect = $isCorrect;
	}
	
}