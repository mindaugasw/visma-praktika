<?php


namespace App\Service\Hyphenator;


use App\Entity\WordResult;

class HyphenationHandler
{
    
    private Hyphenator $hyphenator;
    
    public function __construct(Hyphenator $hyphenator)
    {
        $this->hyphenator = $hyphenator;
    }
    
    public function processText(string $text): string
    {
        // TODO
    }
    
    public function processOneWord(): WordResult
    {
        // TODO
    }
}