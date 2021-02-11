<?php

namespace App\Repository;

use App\Entity\WordResult;
use App\Service\DBConnection;
use App\Service\PsrLogger\LoggerInterface;
use Exception;

class WordResultRepository
{
    const BATCH_IMPORT_SIZE = 5000;
    
    const TABLE = 'word';
    
    private DBConnection $db;
    private WordToPatternRepository $wtpRepo;
    private LoggerInterface $logger;
    
    public function __construct(DBConnection $db, WordToPatternRepository $wtpRepo, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->wtpRepo = $wtpRepo;
        $this->logger = $logger;
    }
    
    public function truncate(): void
    {
        $truncateSql = sprintf('DELETE FROM `%s`', self::TABLE); // can't TRUNCATE cause of FK
        if (!$this->db->query($truncateSql))
            throw new Exception();
    }
    
    /**
     * @param array<WordResult> $words
     */
    public function import(array $words): void
    {
        $sqlHeader = sprintf('INSERT INTO `%s`(`id`, `input`, `result`) VALUES ', self::TABLE);
        $wordsSql = $sqlHeader;
        $wordsArgs = [];
        $lastCommitIndex = 0;
        
        for ($i = 0; $i < count($words); $i++) {
    
            $words[$i]->setId($i + 1);
            $wordsSql .= '(?, ?, ?),';
            array_push($wordsArgs, $words[$i]->getId(), $words[$i]->getInput(), $words[$i]->getResult());
            
            if ($i === count($words) - 1 ||                         // always commit on last iteration
                ($i % self::BATCH_IMPORT_SIZE === 0 && $i !== 0)) { // split big import into multiple statements and transactions
    
                $wordsSql = substr($wordsSql, 0, -1); // remove trailing comma
                
                // build data for M:M table word_to_pattern
                [$wtpSql, $wtpArgs] = $this->wtpRepo->buildImportQuery(array_slice($words, $lastCommitIndex, $i - $lastCommitIndex));
                
                $this->db->beginTransaction();
                if (!$this->db->query($wordsSql, $wordsArgs)) // insert words batch
                    throw new Exception();
                
                if (!$this->db->query($wtpSql, $wtpArgs)) // insert patterns for those words
                    throw new Exception();
                $this->db->commitTransaction();
                
                $this->logger->debug('Saved to DB %d/%d words', [$i + 1, count($words)]);
                
                $wordsSql = $sqlHeader;
                $wordsArgs = [];
                $lastCommitIndex = $i;
            }
        }
    }
    
    public function findOne(string $inputWord): ?WordResult
    {
        $wordSql = sprintf('SELECT * FROM `%s` WHERE `input`=?', self::TABLE);
        $resultsArray = $this->db->fetchClass($wordSql, [$inputWord], WordResult::class);
        
        if (count($resultsArray) === 0)
            return null;
        
        $wordResult = $resultsArray[0];
    
        $wordResult->setMatchedPatterns(
            $this->wtpRepo->findByWord($wordResult->getId())
        );
        
        return $wordResult;
    }
}
