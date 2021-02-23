<?php

declare(strict_types=1);

namespace Tests\Service\Hyphenator;

use App\DataStructure\HashTable;
use App\DataStructure\TextSearchInterface;
use App\DataStructure\Trie\Trie;
use App\Entity\WordInput;
use App\Service\Hyphenator\Hyphenator;
use PHPUnit\Framework\TestCase;

class HyphenatorTest extends TestCase
{
    private TextSearchInterface $searchTree;
    private TextSearchInterface $searchHt;
    
    protected function setUp(): void
    {
        parent::setUp();
        $dataProvider = new HyphenationDataProvider();
        $this->searchTree = Trie::constructFromArray(
            $dataProvider->getFewPatternsDictionary()
        );
        $this->searchHt = HashTable::constructFromArray(
            $dataProvider->getFewPatternsDictionary()
        );
    }
    
    /**
     * @dataProvider Tests\Service\Hyphenator\HyphenationDataProvider::getFewSingleWordsData
     * @param string $inputWord
     * @param string $expectedResult
     */
    public function testHyphenator(string $inputWord, string $expectedResult): void
    {
        $hyphenator = new Hyphenator();
        
        // Test with both data structures
        $this->assertSame(
            $expectedResult,
            $hyphenator->wordToSyllables(
                new WordInput($inputWord),
                $this->searchTree
            )->getResult(),
            'Not working with tree search'
        );
    
        $this->assertSame(
            $expectedResult,
            $hyphenator->wordToSyllables(
                new WordInput($inputWord),
                $this->searchHt
            )->getResult(),
            'Not working with hashtable search'
        );
    }
}
