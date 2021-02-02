<?php

class HyphenationPattern
{
	/** @var string Full Hyphenation pattern, e.g. .mis1*/
	public string $pattern;
	
	/** @var string Pattern without start or end dots, e.g. mis1 */
	public string $patternNoDot;
	
	/** @var string Only pattern text, e.g. mis */
	public string $patternText;
	
	/** @var bool Is it word start pattern? e.g. .mis1 */
	public bool $isStartPattern;
	
	/** @var bool Is it word end pattern? e.g. 4te. */
	public bool $isEndPattern;
	
	/** @var int Position in word at which this pattern starts */
	public int $position;
	
	public function __construct($pattern)
	{
		$this->pattern = $pattern;
		$this->patternNoDot = strval(preg_replace('/\./', '', $pattern)); // TODO strval remove
		$this->patternText = strval(preg_replace('/[\.\d]/', '', $pattern));
		$this->isStartPattern = substr($pattern, 0, 1) === '.';
		$this->isEndPattern = substr($pattern, -1) === '.';
	}
	
}