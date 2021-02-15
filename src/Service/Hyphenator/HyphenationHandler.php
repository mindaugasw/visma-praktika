<?php


namespace App\Service\Hyphenator;


use App\Entity\WordInput;
use App\Entity\WordResult;
use App\Repository\WordResultRepository;
use App\Service\InputReader;

class HyphenationHandler
{
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
        // separate text into words
        $wordMatches = []; // words with their positions in text [[word, pos], ...]
        preg_match_all(self::REGEX_WORD_SEPARATOR, $text, $wordMatches, PREG_OFFSET_CAPTURE);
        $wordMatches = $wordMatches[0]; // remove extra nesting
        
        // map to 1D array and remove match position data for DB querying
        $wordStrings = array_map(function ($match) {
            return $match[0];
        }, $wordMatches);
        $wordResults = $this->wordRepo->findMany($wordStrings);
        
        
        $newWords = []; // words found in $text but not in DB
        
        // filter out new words (those not found in DB).
        // insert them in correct places in $wordResults
        // (and also save to $newWords)
        for ($i = 0; $i < count($wordStrings); $i++) {
            if (!isset($wordResults[$wordStrings[$i]])) { // if word is missing from $wordResults...
                // ...insert $wordString in its position
                //array_splice($wordResults, $i, 0, [$wordStrings[$i] => ''], true);
                $wordResults = array_slice($wordResults, 0, $i, true) // TODO not care about ordering
                    + [$wordStrings[$i] => null]
                    + array_slice($wordResults, $i, null, true);
                $newWords[] = $wordStrings[$i];
            }
        }
        
        // hyphenate all new words
        [$array, $tree] = $this->reader->getPatternMatchers(count($newWords) > 6 ? 'tree' : 'array');
        for ($i = 0; $i < count($newWords); $i++) {
            $wordResult = $this->hyphenator->wordToSyllables(
                new WordInput($newWords[$i]),
                $array,
                $tree
            );
            // replace string with WordResult object in results and new words
            $wordResults[$newWords[$i]] = $wordResult;
            $newWords[$i] = $wordResult;
        }
        
        // replace all words in text with hyphenated ones
        $originalTextLength = strlen($text);
        foreach ($wordMatches as $match) {
            $text = substr_replace(
                $text, // full text block, in which to replace
                $wordResults[$match[0]], // new string to replace with
                strlen($text) - $originalTextLength + $match[1] // start index. Accounts for moved index due already replaced words
            );
        }
        
        // insert new words to DB
        $this->wordRepo->insertMany();
        
        
        die('asdf');
    }
}
