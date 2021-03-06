<?php

declare(strict_types=1);

namespace App\Service\Hyphenator;

use App\DataStructure\HashTable;
use App\DataStructure\Trie\Trie;
use App\Entity\WordInput;
use App\Entity\WordResult;
use App\Repository\WordResultRepository;
use App\Service\InputReader;

/**
 * A middle-man functionality between hyphenation algorithm and all other services.
 * Provides methods for word hyphenation, automatically choosing patterns source
 * (DB or file) and saving new words to DB
 * @package App\Service\Hyphenator
 */
class HyphenationHandler
{
    /**
     * preg_match_all to get array of words from text block
     */
    private const REGEX_WORD_SEPARATOR = '/\b[a-zA-Z\-\'’]{2,}\b/';
    
    private Hyphenator $hyphenator;
    private WordResultRepository $wordRepo;
    private InputReader $reader;
    
    public function __construct(Hyphenator $hyphenator, WordResultRepository $wordRepo, InputReader $reader)
    {
        $this->hyphenator = $hyphenator;
        $this->wordRepo = $wordRepo;
        $this->reader = $reader;
    }
    
    /**
     * Hyphenate a single word
     * Search in DB first and if not found then load hyphenation data, hyphenate
     * the word, and save it to DB
     * @param string $input
     * @return WordResult
     */
    public function processOneWord(string $input): WordResult
    {
        // find in db
        $wordResult = $this->wordRepo->findOneByInput($input);
        if ($wordResult !== null) {
            return $wordResult;
        } else {
            // hyphenate new word
            $searchDS = $this->reader->getPatternSearchDS(HashTable::class);
            $wordResult = $this->hyphenator->wordToSyllables(new WordInput($input), $searchDS);
            $this->wordRepo->insertOne($wordResult);
            return $wordResult;
        }
    }
    
    /**
     * Hyphenate a block of text
     * @param string $text
     * @return string
     */
    public function processText(string $text): string
    {
        $text = strtolower($text); // TODO change algorithm to ignore letter case
        
        // separate text into words
        $wordMatches = $this->separateTextIntoWords($text);
        
        // map to 1D array and prepare for DB querying (remove match position data, duplicate values)
        $wordStrings = $this->convertMatchesTo1DArray($wordMatches);
        
        // query DB
        $wordResults = $this->wordRepo->findMany($wordStrings);
        
        //select new words
        $newWords = $this->filterOutNewWords($wordStrings, $wordResults);
        
        // hyphenate all new words
        $hyphenatedNewWords = $this->hyphenateNewWords($newWords, $wordResults);
        
        // replace all words in text with hyphenated ones
        $hyphenatedText = $this->replaceTextWithHyphenatedWords($text, $wordMatches, $wordResults);
        
        // insert new words to DB
        if (count($hyphenatedNewWords) > 0) {
            $this->wordRepo->insertMany($hyphenatedNewWords);
        }
        
        return $hyphenatedText;
    }
    
    // Text processing helper methods
    
    /**
     * 1st algorithm step
     * Separate block of text into words.
     * Returns array of regex matches (word and its position in text) in the
     * format: [[word, pos], ...]
     *
     * @param  string $text
     * @return array<array<string, int>> Regex matches
     */
    private function separateTextIntoWords(string $text): array
    {
        $wordMatches = []; // words with their positions in text [[word, pos], ...]
        preg_match_all(self::REGEX_WORD_SEPARATOR, $text, $wordMatches, PREG_OFFSET_CAPTURE);
        $wordMatches = $wordMatches[0]; // remove extra nesting
        
        return $wordMatches;
    }
    
    /**
     * 2nd algorithm step
     * Convert regex matches from 1st step to 1D words array for querying DB.
     * Filters array to contain only unique items.
     *
     * @param  array<array<string, int>> $wordMatches Regex matches from 1st step
     * @return array<string> Array of words found in text block
     */
    private function convertMatchesTo1DArray(array $wordMatches): array
    {
        $wordStrings = array_map(
            function ($match) {
                return $match[0];
            },
            $wordMatches
        );
        
        // array_values to reindex array, as array_unique doesn't do that
        $wordStrings = array_values(array_unique($wordStrings));
        return $wordStrings;
    }
    
    /**
     * 3rd algorithm step
     * Select new words from this text block (those not found in DB).
     * Add keys for those words to $wordResults.
     * Make new array of only words - $newWords.
     *
     * @param  array<string>     $wordStrings All words in text block
     * @param  array<WordResult> $wordResults Words found in DB. Original array
     * will be mutated (added keys for missing words)
     * @return array<string> Array of words existing in text but not in DB
     */
    private function filterOutNewWords(array $wordStrings, array &$wordResults): array
    {
        $newWords = []; // words found in $text but not in DB
        
        // filter out new words (those not found in DB).
        // add them to $wordResults and $newWords
        foreach ($wordStrings as $wordInputString) {
            if (!isset($wordResults[$wordInputString])) {
                $wordResults[$wordInputString] = null;
                $newWords[] = $wordInputString;
            }
        }
        
        return $newWords;
    }
    
    /**
     * 4th algorithm step
     * Hyphenate all $newWords, insert them to $wordResults and return new array
     * of hyphenated new words WordResult objects, for insertion to DB
     *
     * @param  array<string>            $newWords    New words, found in text but not in DB
     * @param  array<WordResult|string> $wordResults array from 3rd step, WordResults
     * from DB joined with $newWords strings
     * @return array<WordResult> Array of old and new words, all hyphenated, as WordResult
     */
    private function hyphenateNewWords(array $newWords, array &$wordResults): array
    {
        $hyphenatedNewWords = [];
        
        $searchDS = $this->reader->getPatternSearchDS(
            count($newWords) > 6 ?
                Trie::class :
                HashTable::class
        );
        
        foreach ($newWords as $newWord) {
            $wordResult = $this->hyphenator->wordToSyllables(
                new WordInput($newWord),
                $searchDS
            );
            
            $hyphenatedNewWords[] = $wordResult;
            $wordResults[$newWord] = $wordResult;
        }
        
        return $hyphenatedNewWords;
    }
    
    /**
     * 5th algorithm step
     * Modify $text to replace all found words with their hyphenated versions
     *
     * @param  string                    $text        Original text block
     * @param  array<array<string, int>> $wordMatches Regex word matches array, from 1st step
     * @param  array<WordResult>         $wordResults All words hyphenated, from 4th step
     * @return string hyphenated text block
     */
    private function replaceTextWithHyphenatedWords(string $text, array $wordMatches, array $wordResults): string
    {
        $originalTextLength = strlen($text);
        foreach ($wordMatches as $match) {
            $text = substr_replace(
                $text, // full text block, in which to replace
                $wordResults[$match[0]]->getResult(), // new string to replace with
                strlen($text) - $originalTextLength + $match[1],
                // start index. Accounts for moved index due already replaced words
                strlen($match[0]) // length of the input word (how much to replace)
            );
        }
        
        return $text;
    }
}
