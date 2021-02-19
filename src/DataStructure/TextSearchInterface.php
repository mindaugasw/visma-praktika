<?php

namespace App\DataStructure;

use App\Entity\HyphenationPattern;
use App\Entity\WordInput;

/**
 * Interface for data structures that provide text search
 */
interface TextSearchInterface
{
    /**
     * Construct new search object from $array of HyphenationPatterns
     *
     * @param  array<HyphenationPattern> $array
     * @return static
     */
    public static function constructFromArray(array $array): static;
    
    /**
     * Construct new search object from given $dictionary
     *
     * @param  array<string, HyphenationPattern> $dictionary [$searchKey => $value, ...]
     * @return static
     */
    public static function constructFromDictionary(array $dictionary): static;
    
    /**
     * Add new value
     *
     * @param string $key   Key for this value
     * @param object $value Value that will be returned when $key matches
     */
    public function addValue(string $key, mixed $value): void;
    
    /**
     * Get array of all matches for given text
     *
     * @param  WordInput $wordInput Word in which to search
     * @return array<HyphenationPattern> Array of matched patterns
     */
    public function findMatches(WordInput $wordInput): mixed;
}
