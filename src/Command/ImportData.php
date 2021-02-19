<?php declare(strict_types=1);

namespace App\Command;

use App\DataStructure\Trie\Trie;
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
    // CLI args keys:
    /**
     * --patterns, -p, optional. Patterns import options
     *
     * Values:
     * - true - import default patterns file (default)
     * - false - skip pattern import
     * - filePath - import specific file
     */
    public const ARG_PATTERNS_FILE = 'patterns';
    /**
     * --words, -w, optional. Words import options
     *
     * Values:
     * - true - import default words file (default)
     * - filePath - import specific file
     */
    public const ARG_WORDS_FILE = 'words';
    
    public const WORDS_PER_BATCH = 10_000;
    
    public function __construct(
        private ArgsHandler $argsHandler,
        private InputReader $reader,
        private LoggerInterface $logger,
        private Hyphenator $hyphenator,
        private HyphenationPatternRepository $patternRepo,
        private WordToPatternRepository $wtpRepo,
        private WordResultRepository $wordRepo
    ) {
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
        $filePathArg = $this->argsHandler->get(self::ARG_PATTERNS_FILE, 'true');
    
        // choose mode: true - default file, false - skip, filePath - custom file
        if ($filePathArg === 'false') {
            $this->logger->info('Skipping patterns import');
            return;
        } elseif ($filePathArg === 'true') {
            $this->logger->info('Importing default patterns file');
            $patterns = $this->reader->getPatternArray(false);
        } else {
            $this->logger->info('Importing custom patterns file');
            if (!file_exists($filePathArg)) {
                throw new Exception(sprintf('File does not exist: "%s"', $filePathArg));
            }
            $patterns = $this->reader->getPatternArray(false, $filePathArg);
        }
        
        $this->wtpRepo->truncate();
        $this->patternRepo->truncate();
        $this->patternRepo->import($patterns);
    }
    
    private function importWords(): void
    {
        $filePathArg = $this->argsHandler->get(self::ARG_WORDS_FILE, 'true');
        
        // choose mode: true - default file, filePath - custom file
        if ($filePathArg === 'true') {
            $this->logger->info('Importing default words file');
            $words = $this->reader->getWordList();
        } else {
            $this->logger->info('Importing custom words file');
            if (!file_exists($filePathArg)) {
                throw new Exception(sprintf('File does not exist: "%s"', $filePathArg));
            }
            $words = $this->reader->getWordList($filePathArg);
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
        $searchDS = $this->reader->getPatternSearchDS(Trie::class);
        $this->logger->debug('Hyphenating %d words', [count($words)]);
        $wordResults = [];
        
        Profiler::start('total');
        Profiler::start('wordProcessing');
        for ($i = 0; $i < count($words); $i++) {
            // import everything in batches of WORDS_PER_BATCH
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
            
            $wordResults[] = $this->hyphenator->wordToSyllables($words[$i], $searchDS);
        }
        
        $this->logger->debug('Hyphenated %d words in %f s', [count($wordResults), Profiler::stop('total', 's')]);
        return $wordResults;
    }
}
