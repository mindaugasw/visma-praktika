<?php

namespace App\Command;

use App\Entity\WordInput;
use App\Entity\WordResult;
use App\Repository\HyphenationPatternRepository;
use App\Repository\WordResultRepository;
use App\Repository\WordToPatternRepository;
use App\Service\ArgsHandler;
use App\Service\Hyphenator\Hyphenator;
use App\Service\InputReader;
use App\Service\Profiler;
use Exception;
use Psr\Log\LoggerInterface;

class ImportData implements CommandInterface
{
    // CLI Args:
    /**
     * --patterns, -p, optional. Patterns import options
     * Values:
     * - true - import default patterns file, (default)
     * - false - skip pattern import
     * - file path - import specific file
     */
    const ARG_PATTERNS_FILE = 'patterns';
    /**
     * --words, -w, optional. Words import options
     * Values:
     * - true - import default words file, (default)
     * - false - skip word import
     * - file path - import specific file
     */
    const ARG_WORDS_FILE = 'words';
    
    const WORDS_PER_BATCH = 10_000;
    
    private ArgsHandler $argsHandler;
    private InputReader $reader;
    private LoggerInterface $logger;
    private Hyphenator $hyphenator;
    private HyphenationPatternRepository $patternRepo;
    private WordToPatternRepository $wtpRepo;
    private WordResultRepository $wordRepo;
    
    public function __construct(
        ArgsHandler $argsHandler,
        InputReader $reader,
        LoggerInterface $logger,
        Hyphenator $hyphenator,
        HyphenationPatternRepository $patternRepo,
        WordToPatternRepository $wtpRepo,
        WordResultRepository $wordRepo
    ) {
        $this->argsHandler = $argsHandler;
        $this->reader = $reader;
        $this->logger = $logger;
        $this->hyphenator = $hyphenator;
        $this->patternRepo = $patternRepo;
        $this->wtpRepo = $wtpRepo;
        $this->wordRepo = $wordRepo;
    
        $argsHandler->addArgConfig(self::ARG_PATTERNS_FILE, 'p');
        $argsHandler->addArgConfig(self::ARG_WORDS_FILE, 'w');
    }
    
    public function process(): void
    {
        $this->importPatterns();
        $this->importWords();
    }
    
    private function importPatterns(): void
    {
        $argVal = $this->argsHandler->get(self::ARG_PATTERNS_FILE, 'true');
        $patterns = [];
    
        // choose mode: true - default file, false - skip, file path - custom file
        if ($argVal === 'false') {
            $this->logger->info('Skipping patterns import');
            return;
        } elseif ($argVal === 'true') {
            $this->logger->info('Importing default patterns file');
            $patterns = $this->reader->getPatternArray(false);
        } else {
            $this->logger->info('Importing custom patterns file');
            if (!file_exists($argVal)) {
                throw new Exception(sprintf('File does not exist: "%s"', $argVal));
            }
            $patterns = $this->reader->getPatternArray(false, $argVal);
        }
        
        $this->wtpRepo->truncate();
        $this->patternRepo->truncate();
        $this->patternRepo->import($patterns);
    }
    
    private function importWords(): void
    {
        $argVal = $this->argsHandler->get(self::ARG_WORDS_FILE, 'true');
        $words = [];
        
        // choose mode: true - default file, file path - custom file
        if ($argVal === 'true') {
            $this->logger->info('Importing default words file');
            $words = $this->reader->getWordList();
        } else {
            $this->logger->info('Importing custom words file');
            if (!file_exists($argVal)) {
                throw new Exception(sprintf('File does not exist: "%s"', $argVal));
            }
            $words = $this->reader->getWordList($argVal);
        }
        
        $wordResults = $this->hyphenateWords($words);
        
        $this->wtpRepo->truncate(); // truncated again in case truncate in pattern import was skipped
        $this->wordRepo->truncate();
        $this->wordRepo->insertMany($wordResults);
        $this->logger->info('Import successful');
    }
    
    /**
     * @param  array<WordInput> $words
     * @return array<WordResult>
     */
    private function hyphenateWords(array $words): array
    {
        // clear cache to force get patterns from DB, with their IDs included as
        // cache may still be storing patterns read from file, without IDs
        $this->reader->clearPatternsCache();
        [$array, $tree] = $this->reader->getPatternMatchers('tree');
        $this->logger->debug('Hyphenating %d words', [count($words)]);
        $wordResults = [];
        
        Profiler::start('total');
        Profiler::start('wordProcessing');
        for ($i = 0; $i < count($words); $i++) {
            if ($i % self::WORDS_PER_BATCH === 0 && $i !== 0) {
                $time = Profiler::stop('wordProcessing', 's');
                $this->logger->debug(
                    'Done %d/%d, in %f s, %f ms / word',
                    [
                        $i,
                        count($words),
                        $time, $time / self::WORDS_PER_BATCH * 1000
                    ]
                );
                Profiler::start('wordProcessing');
            }
            
            $wordResults[] = $this->hyphenator->wordToSyllables($words[$i], $array, $tree);
        }
        $this->logger->debug('Hyphenated %d words in %f s', [count($wordResults), Profiler::stop('total', 's')]);
        return $wordResults;
    }
}
