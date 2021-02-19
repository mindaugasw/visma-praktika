<?php

namespace App\Entity;

class HyphenationPattern implements \JsonSerializable
{
    // Pattern types, depending on dot position in the pattern
    public const TYPE_REGULAR = 0;
    public const TYPE_START = 1;
    public const TYPE_END = 2;
    
    /**
     * @var int Id in DB
     */
    private int $id;
    
    /**
     * @var string Full Hyphenation pattern, e.g. .mis1
     */
    private string $pattern;
    
    /**
     * @var string Pattern without start or end dots, e.g. mis1
     */
    private string $patternNoDot;
    
    /**
     * @var string Pattern without any numbers, e.g. .mis
     */
    private string $patternNoNumbers;
    
    /**
     * @var string Only pattern text, e.g. mis
     */
    private string $patternText;
    
    /**
     * @var int TYPE_REGULAR|TYPE_START|TYPE_END
     */
    private int $patternType;
    
    /**
     * @var int Position in word at which this pattern starts
     */
    private int $position;
    
    /**
     * @param ?string $pattern If null, no properties will be set. Meant for initialization with PDO
     */
    public function __construct(?string $pattern = null)
    {
        if ($pattern !== null) {
            $this->pattern = $pattern;
            $this->patternNoDot = str_replace('.', '', $pattern);
            $this->patternNoNumbers = strval(preg_replace('/[\d]/', '', $pattern)); // TODO strval remove
            $this->patternText = str_replace('.', '', $this->patternNoNumbers);
    
            if (substr($pattern, 0, 1) === '.') {
                $this->patternType = self::TYPE_START;
            } elseif (substr($pattern, -1) === '.') {
                $this->patternType = self::TYPE_END;
            } else {
                $this->patternType = self::TYPE_REGULAR;
            }
        }
    }
    
    public function __toString()
    {
        if (isset($this->position)) {
            return sprintf('%s @ %d', $this->pattern, $this->position);
        } else {
            return $this->pattern;
        }
    }
    
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
    
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
    public function getPatternNoNumbers(): string
    {
        return $this->patternNoNumbers;
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
