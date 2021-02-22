<?php declare(strict_types = 1);

namespace Tests\Service\Hyphenator;

use App\Repository\WordResultRepository;
use App\Service\DIContainer\Container;
use App\Service\Hyphenator\HyphenationHandler;
use App\Service\Hyphenator\Hyphenator;
use App\Service\InputReader;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class HyphenatorTest extends TestCase
{
 
    private static Container $diContainer;
    private Hyphenator $hyphenator;
    private WordResultRepository|Stub $wordRepoStub;
    private InputReader $reader;
    private HyphenationHandler $hyphenationHandler;
    
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$diContainer = new Container();
    }
    
    public function setUp(): void
    {
        parent::setUp();
        $this->hyphenator = self::$diContainer->get(Hyphenator::class);
        $this->wordRepoStub = $this->createWordResultRepoStub();
        $this->reader = self::$diContainer->get(InputReader::class);
    
        $this->hyphenationHandler = new HyphenationHandler(
            $this->hyphenator,
            $this->wordRepoStub,
            $this->reader
        );
    }
    
    /**
     * @dataProvider getHyphenationSingleWords
     * @param string $input
     * @param string $expectedResult
     */
    public function testHyphenatorSingleWord(string $input, string $expectedResult): void
    {
        $actualResult = $this->hyphenationHandler->processOneWord($input)->getResult();
        
        $this->assertSame(
            $expectedResult,
            $actualResult
        );
    }
    
    /**
     * @dataProvider getHyphenationTexts
     * @param string $input
     * @param string $expectedResult
     */
    public function testHyphenatorText(string $input, string $expectedResult): void
    {
        $actualResult = $this->hyphenationHandler->processText($input);
        
        $this->assertSame(
            $expectedResult,
            $actualResult
        );
    }
    
    public function getHyphenationSingleWords(): array
    {
        return [
            ['mistranslate', 'mis-trans-late'],
            ['vigorous', 'vig-or-ous'],
            ['changed', 'changed'],
            ['pitch', 'pitch'],
            ['uncopyrightable', 'un-copy-rightable'],
            ['system', 'sys-tem'],
            ['disastrous', 'dis-as-trous'],
            ['frightening', 'fright-en-ing'],
            ['encouraging', 'en-cour-ag-ing'],
        ];
    }
    
    public function getHyphenationTexts(): array
    {
        return [
            [
                'If once you start down the dark path, forever will it dominate your destiny, consume you '
                .'it will, as it did Obi-Wan’s apprentice.',
                'if once y-ou s-tart down the dark- path, for-ev-er will it dom-i-nate y-our des-tiny, con-sume y-ou '
                .'it will, as it did o-bi-wan’s ap-pren-tice.'],
            [
                'Most wels catfish are mainly about 1.3–1.6 m (4 ft 3 in–5 ft 3 in) long; fish longer than 2 m '
                .'(6 ft 7 in) are normally a rarity.',
                'most wel-s cat-fish are main-ly about 1.3–1.6 m (4 ft 3 in-–5 ft 3 in-) long; fish longer than 2 m '
                .'(6 ft 7 in-) are nor-mal-ly a rar-i-ty.'
            ]
        ];
    }
    
    private function createWordResultRepoStub(): Stub|WordResultRepository
    {
        $stub = $this->createStub(WordResultRepository::class);
        $stub->method('findOneByInput')
            ->willReturn(null);
        $stub->method('findMany')
            ->willReturn([]);
        
        return $stub;
    }
}
