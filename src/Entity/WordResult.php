<?php

namespace App\Entity;

class WordResult extends WordInput
{
    /** @var int Id in DB */
    private int $id;
    
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
	
	
	public function __construct(WordInput $input)
	{
		parent::__construct($input->getInput(), $input->getExpectedResult());
		$this->matchedPatterns = [];
		$this->numberMatrix = [];
	}
    
	public function __toString()
    {
        return sprintf('%s -> %s', $this->input, $this->result);
    }
    
    /**
     * Id from DB
     * @return int
     */
	public function getId(): int
    {
        return $this->id;
    }
    
    /**
     * Id from DB. Allows setting only once (readonly property)
     * @param int $id
     */
    public function setId(int $id): void
    {
        if (isset($this->id))
            throw new \Exception('Attempted to reset object id');
        $this->id = $id;
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
     * Final result - word divided into syllables, e.g. mis-trans-late
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
     * Result with spaces, e.g. m i s-t r a n s-l a t e
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
     * Result with numbers, e.g. m2i s1t4r a2n2s3l2a4t e
	 * @param string $resultWithNumbers
	 */
	public function setResultWithNumbers(string $resultWithNumbers): void
	{
		$this->resultWithNumbers = $resultWithNumbers;
	}
	
	/**
	 * Get all patterns found in this word
	 * @return array<HyphenationPattern>
	 */
	public function getMatchedPatterns(): array
	{
		return $this->matchedPatterns;
	}
	
	/**
     * Add a single matched pattern to the array
	 * @param HyphenationPattern $pattern
	 */
	public function addMatchedPattern(HyphenationPattern $pattern): void
	{
		$this->matchedPatterns[] = $pattern;
	}
    
    /**
     * Set whole array of matched patterns
     * @param array<HyphenationPattern> $patterns
     */
	public function setMatchedPatterns(array $patterns): void
    {
        $this->matchedPatterns = $patterns;
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
     * Add a single row to the number matrix
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
     * Processing time, ms
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
		return $this->result === $this->getExpectedResult();
	}
	
}