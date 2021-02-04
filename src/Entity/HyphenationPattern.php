<?php

namespace App\Entity;

class HyphenationPattern
{
	// Pattern types, depending on dot position in the pattern
	private const TYPE_REGULAR = 0;
	private const TYPE_START = 1;
	private const TYPE_END = 2;
	
	/** @var string Full Hyphenation pattern, e.g. .mis1*/
	private string $pattern;
	
	/** @var string Pattern without start or end dots, e.g. mis1 */
	private string $patternNoDot;
	
	/** @var string Only pattern text, e.g. mis */
	private string $patternText;
	
	/** @var int TYPE_REGULAR|TYPE_START|TYPE_END */
	private int $patternType;
	
	/** @var int Position in word at which this pattern starts */
	private int $position;
	
	
	public function __construct($pattern)
	{
		$this->pattern = $pattern;
		$this->patternNoDot = strval(preg_replace('/\./', '', $pattern)); // TODO strval remove
		$this->patternText = strval(preg_replace('/[\d]/', '', $this->patternNoDot));
		
		if (substr($pattern, 0, 1) === '.') 
			$this->patternType = self::TYPE_START;
		else if (substr($pattern, -1) === '.')
			$this->patternType = self::TYPE_END;
		else
			$this->patternType = self::TYPE_REGULAR;
	}
	
	/**
	 * @return string
	 */
	public function getPattern(): string
	{
		return $this->pattern;
	}
	
	/**
	 * @return string
	 */
	public function getPatternNoDot(): string
	{
		return $this->patternNoDot;
	}
	
	/**
	 * @return string
	 */
	public function getPatternText(): string
	{
		return $this->patternText;
	}
	
	/**
	 * @return bool
	 */
	public function isStartPattern(): bool
	{
		return $this->patternType === self::TYPE_START;
	}
	
	/**
	 * @return bool
	 */
	public function isEndPattern(): bool
	{
		return $this->patternType === self::TYPE_END;
	}
		
	/**
	 * @return int
	 */
	public function getPosition(): int
	{
		return $this->position;
	}
	
	/**
	 * @param int $position
	 */
	public function setPosition(int $position): void
	{
		$this->position = $position;
	}
	
}