<?php


namespace App\Service\Hyphenator;


use App\Entity\WordInput;
use App\Entity\WordResult;
use App\Repository\WordResultRepository;
use App\Service\InputReader;

class HyphenationHandler
{
    private Hyphenator $hyphenator;
    private WordResultRepository $wordRepo;
    /**
     * @var InputReader
     */
    private InputReader $reader;
    
    public function __construct(Hyphenator $hyphenator, WordResultRepository $wordRepo, InputReader $reader)
    {
        $this->hyphenator = $hyphenator;
        $this->wordRepo = $wordRepo;
        $this->reader = $reader;
    }
    
    public function processText(string $text): string
    {
        // TODO
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
}
