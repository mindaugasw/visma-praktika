<?php


namespace App\Service\Hyphenator;


use App\Entity\WordInput;
use App\Entity\WordResult;
use App\Repository\WordResultRepository;
use App\Service\InputReader;

class HyphenationHandler
{
    /**
     * preg_match_all to get array of words from text block
     */
    const REGEX_WORD_SEPARATOR = '/\b[a-zA-Z\-\'’]{2,}\b/';
    
    private Hyphenator $hyphenator;
    private WordResultRepository $wordRepo;
    private InputReader $reader;
    
    public function __construct(Hyphenator $hyphenator, WordResultRepository $wordRepo, InputReader $reader)
    {
        $this->hyphenator = $hyphenator;
        $this->wordRepo = $wordRepo;
        $this->reader = $reader;
    }
    
    public function processOneWord(string $input): WordResult
    {
        $wordResult = $this->wordRepo->findOne($input);
        if ($wordResult !== null) {
            return $wordResult;
        } else {
            [$array, $tree] = $this->reader->getPatternMatchers('array');
            $wordResult = $this->hyphenator->wordToSyllables(new WordInput($input), $array, $tree);
            $this->wordRepo->insertOne($wordResult);
            return $wordResult;
        }
    }
    
    public function processText(string $text): string
    {
        $text = strtolower($text); // TODO change algorithm to ignore letter case
        
        // separate text into words
        $wordMatches = $this->separateTextIntoWords($text);
        
        // map to 1D array and remove match position data for DB querying
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
     * @param string $text
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
     * Convert regex matches from 1st step to 1D words array for querying DB
     * @param array<array<string, int>> $wordMatches Regex matches from 1st step
     * @return array<string> Array of words found in text block
     */
    private function convertMatchesTo1DArray(array $wordMatches): array
    {
        return array_map(function ($match) {
            return $match[0];
        }, $wordMatches);
    }
    
    /**
     * 3rd algorithm step
     * Select new words from this text block (those not found in DB).
     * Add keys for those words to $wordResults.
     * Make new array of only words - $newWords. 
     * @param array<string> $wordStrings All words in text block
     * @param array<WordResult> $wordResults Words found in DB. Original array will be mutated (added keys for missing words)
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
     * @param array<string> $newWords New words, found in text but not in DB
     * @param array<WordResult|string> $wordResults array from 3rd step, WordResults from DB joined with $newWords strings
     * @return array<WordResult> Array of old and new words, all hyphenated, as WordResult
     */
    private function hyphenateNewWords(array $newWords, array &$wordResults): array
    {
        $hyphenatedNewWords = [];
        
        [$array, $tree] = $this->reader->getPatternMatchers(count($newWords) > 6 ? 'tree' : 'array'); // only build tree if there's many new words
        
        foreach ($newWords as $newWord) {
            $wordResult = $this->hyphenator->wordToSyllables(
                new WordInput($newWord),
                $array,
                $tree
            );
            
            $hyphenatedNewWords[] = $wordResult;
            $wordResults[$newWord] = $wordResult;
        }
        
        return $hyphenatedNewWords;
    }
    
    /**
     * 5th algorithm step
     * Modify $text to replace all found words with their hyphenated versions
     * @param string $text Original text block
     * @param array<array<string, int>> $wordMatches Regex word matches array, from 1st step
     * @param array<WordResult> $wordResults All words hyphenated, from 4th step
     * @return string hyphenated text block
     */
    private function replaceTextWithHyphenatedWords(string $text, array $wordMatches, array $wordResults): string
    {
        $originalTextLength = strlen($text);
        foreach ($wordMatches as $match) {
            $text = substr_replace(
                $text, // full text block, in which to replace
                $wordResults[$match[0]]->getResult(), // new string to replace with
                strlen($text) - $originalTextLength + $match[1], // start index. Accounts for moved index due already replaced words
                strlen($match[0]) // length of the input word (how much to replace)
            );
        }
        
        return $text;
    }
}
