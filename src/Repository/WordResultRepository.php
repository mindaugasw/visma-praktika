<?php

namespace App\Repository;

use App\Entity\WordResult;
use App\Exception\ServerErrorException;
use App\Service\DBConnection;
use App\Service\PsrLogger\LoggerInterface;
use Exception;

class WordResultRepository
{
    const BATCH_IMPORT_SIZE = 3500;
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
    
    public function findOneByInput(string $inputWord, bool $joinPatterns = true): ?WordResult
    {
        $wordSql = sprintf('SELECT * FROM `%s` WHERE `input`=?', self::TABLE);
        return $this->findOne($wordSql, [$inputWord]);
    }
    
    public function findOneById(int $id, bool $joinPatterns = true): ?WordResult
    {
        $wordSql = sprintf('SELECT * FROM `%s` WHERE `id`=%d', self::TABLE, $id);
        return $this->findOne($wordSql, []);
    }
    
    /**
     * Find a list of words in DB. Returns WordResult object only for found words
     * @param array<string> $words
     * @return array<WordResult> inputWordString => WordResult obj
     */
    public function findMany(array $words): array
    {
        if (count($words) === 0)
            throw new Exception();
                       
        $sql = sprintf(
            'SELECT * FROM `%s` WHERE `input` IN (%s',
            self::TABLE,
            str_repeat('?,', count($words))
        );
        $sql = substr($sql, 0, -1).')'; // remove trailing comma and add closing )
        
        $wordResults = $this->db->fetchClass($sql, $words, WordResult::class);
        $assocResults = []; // assoc results array, inputWordString => WordResult obj
    
        foreach ($wordResults as $result)
            $assocResults[$result->getInput()] = $result;
        
        return $assocResults;
    }
    
    /**
     * @param array<WordResult> $words
     */
    public function insertMany(array $words): void
    {
        $sqlHeader = sprintf('INSERT INTO `%s`(`id`, `input`, `result`) VALUES ', self::TABLE);
        $wordsSql = $sqlHeader;
        $wordsArgs = [];
        $lastCommitIndex = 0;
        $autoIncrementId = $this->db->getNextAutoIncrementId(self::TABLE);
        
        for ($i = 0; $i < count($words); $i++) {
    
            $words[$i]->setId($autoIncrementId + $i);
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
    
    public function insertOne(WordResult $wordResult): void
    {
        $wordResult->setId($this->db->getNextAutoIncrementId(self::TABLE));
        $wordSql = sprintf('INSERT INTO `%s`(`id`, `input`, `result`) VALUES (?,?,?)', self::TABLE);
        $wordArgs = [
            $wordResult->getId(),
            $wordResult->getInput(),
            $wordResult->getResult()
        ];
        
        [$patternsSql, $patternsArgs] = $this->wtpRepo->buildImportQuery([$wordResult]);
        
        $this->db->beginTransaction();
        $lastId = $this->db->insert($wordSql, $wordArgs);
        $patternsArgs[0] = $lastId;
        if (!$this->db->query($patternsSql, $patternsArgs))
            throw new Exception();
        $this->db->commitTransaction();
    }
    
    public function delete(WordResult $wordResult): void
    {
        $sql = sprintf('DELETE FROM `%s` WHERE `id`=%d', self::TABLE, $wordResult->getId());
        $result = $this->db->query($sql);
        
        if ($result === false) {
            throw new ServerErrorException('Could not delete item');
        }
    }
    
    public function truncate(): void
    {
        $truncateSql = sprintf('DELETE FROM `%s`', self::TABLE); // can't TRUNCATE cause of FK
        if (!$this->db->query($truncateSql))
            throw new Exception();
    }
    
    private function findOne(string $sqlQuery, array $sqlArgs, bool $joinPatterns = true): ?WordResult
    {
        $resultsArray = $this->db->fetchClass($sqlQuery, $sqlArgs, WordResult::class);
        
        if (count($resultsArray) === 0)
            return null;
        $wordResult = $resultsArray[0];
        
        if ($joinPatterns) {
            $wordResult->setMatchedPatterns(
                $this->wtpRepo->findByWord($wordResult->getId())
            );
        }
        
        return $wordResult;
    }
}
