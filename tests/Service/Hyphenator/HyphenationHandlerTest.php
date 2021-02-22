<?php

declare(strict_types = 1);

namespace Tests\Service\Hyphenator;

use App\DataStructure\HashTable;
use App\Entity\WordInput;
use App\Entity\WordResult;
use App\Repository\WordResultRepository;
use App\Service\Hyphenator\HyphenationHandler;
use App\Service\Hyphenator\Hyphenator;
use App\Service\InputReader;
use PHPUnit\Framework\MockObject\Stub\Stub;
use PHPUnit\Framework\TestCase;

class HyphenationHandlerTest extends TestCase
{
    private HyphenationHandler $hyphenationHandler;
    private HyphenationDataProvider $dataProvider;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->dataProvider = new HyphenationDataProvider();
        
        $this->hyphenationHandler = new HyphenationHandler(
            $this->createHyphenatorStub(),
            $this->createWordRepoStub(),
            $this->createReaderStub()
        );
    }
    
    /**
     * @dataProvider Tests\Service\Hyphenator\HyphenationDataProvider::getFewSingleWordsData
     * @param string $input
     * @param string $expectedResult
     */
    public function testOneWord(string $input, string $expectedResult): void
    {
        $this->assertSame(
            $expectedResult,
            $this->hyphenationHandler->processOneWord($input)->getResult()
        );
    }
    
    /**
     * @dataProvider Tests\Service\Hyphenator\HyphenationDataProvider::getShortTextsData
     * @param string $input
     * @param string $expectedResult
     */
    public function testTextBlock(string $input, string $expectedResult): void
    {
        $this->assertSame(
            $expectedResult,
            $this->hyphenationHandler->processText($input)
        );
    }
    
    public function createWordRepoStub(): WordResultRepository|Stub
    {
        $wordRepoStub = $this->createStub(WordResultRepository::class);
        $wordRepoStub->method('findOneByInput')
            ->willReturn(null);
        $wordRepoStub->method('findMany')
            ->willReturn([]);
        //$wordRepoStub->method('insertMany') // TODO mock insert methods?
        //    ->;
        
        return $wordRepoStub;
    }
    
    public function createHyphenatorStub(): Hyphenator|Stub
    {
        $returnValuesMap = $this->dataProvider->getFewSingleWordsData();
        
        $hyphenatorStub = $this->createStub(Hyphenator::class);
        $hyphenatorStub->method('wordToSyllables')
            ->willReturnCallback(
                function (WordInput $wordInput) use ($returnValuesMap) {
                    $wordResult = new WordResult($wordInput);
                    $wordResult->setResult($returnValuesMap[$wordInput->getInput()][1]);
                    return $wordResult;
                }
            );
        
        return $hyphenatorStub;
    }
    
    public function createReaderStub(): InputReader|Stub
    {
        $readerStub = $this->createStub(InputReader::class);
        $readerStub->method('getPatternSearchDS')
            ->willReturn(new HashTable());
        
        return $readerStub;
    }
}
