<?php

namespace App\DataStructure;

use App\Entity\HyphenationPattern;
use App\Entity\WordInput;

class HashTable implements TextSearchInterface
{
    /**
     * @var array Internal hash table
     */
    private array $hashTable;
    
    public function __construct()
    {
        $this->hashTable = [];
    }
    
    public static function constructFromArray(array $array): static
    {
        $newObj = new HashTable();
        
        foreach ($array as $element) {
            $newObj->addValue($element->getPattern(), $element);
        }
        
        return $newObj;
    }
    
    /**
     * @inheritDoc
     */
    public static function constructFromDictionary(array $dictionary): static
    {
        $newObj = new HashTable();
        $newObj->hashTable = $dictionary;
        return $newObj;
    }
    
    /**
     * @inheritDoc
     */
    public function findMatches(WordInput $wordInput): array
    {
        $text = $wordInput->getInput();
        $matches = [];
        
        foreach ($this->hashTable as $key => $value) {
            // TODO currently can't find pattern more than once in one word
            $strpos = strpos($text, $value->getPatternText());
            
            if ($strpos !== false) {
                $valueCopy = clone $value; // clone first to not write position info to object in the hash table
                $valueCopy->setPosition($strpos);
                
                if (!$this->isMatchValid($text, $valueCopy)) {
                    // start or end pattern not in correct position
                    continue;
                }
                
                $matches[] = $valueCopy;
            }
        }
        
        return $matches;
    }
    
    /**
     * @inheritDoc
     */
    public function addValue(string $key, mixed $value): void
    {
        $this->hashTable[$key] = $value;
    }
    
    /**
     * Check if match is still valid if pattern is start or end pattern, as matching
     * does not validate match position
     *
     * TODO move to separate wrapper class specifically for HyphenationPatterns
     *      matching? To make HashTable more reusable
     *
     * @param  string             $text    Text on which search was performed
     * @param  HyphenationPattern $pattern Found pattern, with set $position field
     * @return bool Is this match valid?
     */
    private function isMatchValid(string $text, HyphenationPattern $pattern): bool
    {
        // start pattern not at the start
        if ($pattern->isStartPattern() && $pattern->getPosition() !== 0) {
            return false;
        }
        
        // end pattern not at the end
        if (
            ($pattern->isEndPattern()
            && $pattern->getPosition() + strlen($pattern->getPatternText()) !== strlen($text))
        ) {
            return false;
        }
        
        return true;
    }
}
