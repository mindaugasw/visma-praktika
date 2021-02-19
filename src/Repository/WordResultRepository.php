<?php

namespace App\Repository;

use App\Entity\WordResult;
use App\Exception\ServerErrorException;
use App\Service\DB\DBConnection;
use App\Service\DB\QueryBuilder;
use Exception;
use Psr\Log\LoggerInterface;

class WordResultRepository
{
    public const TABLE = 'word';
    
    private const BATCH_IMPORT_SIZE = 3500;
    
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
        $wordSql = (new QueryBuilder())
            ->select('*')
            ->from(self::TABLE)
            ->where('input=?')
            ->getQuery();
        
        return $this->findOne($wordSql, [$inputWord]);
    }
    
    public function findOneById(int $id, bool $joinPatterns = true): ?WordResult
    {
        $wordSql = (new QueryBuilder())
            ->select('*')
            ->from(self::TABLE)
            ->where(sprintf('id=%d', $id))
            ->getQuery();
        
        return $this->findOne($wordSql, []);
    }
    
    /**
     * Find a list of words in DB. Returns WordResult object only for found words
     *
     * @param  array<string> $words
     * @return array<WordResult> inputWordString => WordResult obj
     */
    public function findMany(array $words): array
    {
        if (count($words) === 0) {
            throw new Exception();
        }
        
        // TODO add this functionality to QueryBuilder
        $inQuery = str_repeat('?,', count($words));
        $inQuery = substr($inQuery, 0, -1) . ')'; // remove trailing comma and add closing )
        
        $sql = (new QueryBuilder())
            ->select('*')
            ->from(self::TABLE)
            ->where('input IN (' . $inQuery)
            ->getQuery();
        
        $wordResults = $this->db->fetchClass($sql, $words, WordResult::class);
        $assocResults = []; // assoc results array, inputWordString => WordResult obj
    
        foreach ($wordResults as $result) {
            $assocResults[$result->getInput()] = $result;
        }
        
        return $assocResults;
    }
    
    /**
     * @param array<WordResult> $words
     */
    public function insertMany(array $words): void
    {
        $wordsArgs = [];
        $lastCommitIndex = 0;
        $autoIncrementId = $this->db->getNextAutoIncrementId(self::TABLE);
        
        for ($i = 0; $i < count($words); $i++) {
            // TODO refactor this mess
            $words[$i]->setId($autoIncrementId + $i);
            array_push($wordsArgs, $words[$i]->getId(), $words[$i]->getInput(), $words[$i]->getResult());
    
            // split big import into multiple statements and transactions
            if ($i === count($words) - 1                             // always commit on last iteration
                || ($i % self::BATCH_IMPORT_SIZE === 0 && $i !== 0)
            ) {
                $wordsSql = (new QueryBuilder())
                    ->insertInto(self::TABLE, ['id', 'input', 'result'])
                    ->values('?, ?, ?', $i - $lastCommitIndex + 1)
                    ->getQuery();
                
                // build data for M:M table word_to_pattern
                [$wtpSql, $wtpArgs] = $this->wtpRepo->buildImportQuery(array_slice(
                    $words,
                    max($lastCommitIndex - 1, 0),
                    max($i - $lastCommitIndex, 1)
                ));
                
                $this->db->beginTransaction();
                if (!$this->db->query($wordsSql, $wordsArgs)) { // insert words batch
                    throw new Exception();
                }
                
                if (!$this->db->query($wtpSql, $wtpArgs)) { // insert patterns for those words
                    throw new Exception();
                }
                $this->db->commitTransaction();
                
                $this->logger->debug('Saved to DB %d/%d words', [$i + 1, count($words)]);
                
                $wordsArgs = [];
                $lastCommitIndex = $i + 1;
            }
        }
    }
    
    public function insertOne(WordResult $wordResult): void
    {
        $wordResult->setId($this->db->getNextAutoIncrementId(self::TABLE));
        $wordSql = (new QueryBuilder())
            ->insertInto(self::TABLE, ['id', 'input', 'result'])
            ->values('?, ?, ?', 1)
            ->getQuery();
        
        $wordArgs = [
            $wordResult->getId(),
            $wordResult->getInput(),
            $wordResult->getResult()
        ];
        
        [$patternsSql, $patternsArgs] = $this->wtpRepo->buildImportQuery([$wordResult]);
        
        $this->db->beginTransaction();
        $lastId = $this->db->insert($wordSql, $wordArgs);
        $patternsArgs[0] = $lastId;
        if ($patternsSql !== null) { // null - no matched patterns
            if (!$this->db->query($patternsSql, $patternsArgs)) {
                throw new Exception();
            }
        }
        $this->db->commitTransaction();
    }
    
    public function delete(WordResult $wordResult): void
    {
        $sql = (new QueryBuilder())
            ->delete()
            ->from(self::TABLE)
            ->where(sprintf('id=%d', $wordResult->getId()))
            ->getQuery();
        
        $result = $this->db->query($sql);
        
        if ($result === false) {
            throw new ServerErrorException('Could not delete item');
        }
    }
    
    public function truncate(): void
    {
        $truncateSql = (new QueryBuilder())
            ->delete() // can't TRUNCATE cause of FKs
            ->from(self::TABLE)
            ->getQuery();
        
        if (!$this->db->query($truncateSql)) {
            throw new Exception();
        }
    }
    
    /**
     * Execute give query for selecting one item and join it together with matched
     * patterns
     *
     * @param  string $sqlQuery     SELECT query to execute
     * @param  array  $sqlArgs      args for $sqlQuery
     * @param  bool   $joinPatterns if true, will embed HyphenationPattern objects
     *                              into WordResult->matchedPatterns
     * @return ?WordResult
     */
    private function findOne(string $sqlQuery, array $sqlArgs, bool $joinPatterns = true): ?WordResult
    {
        $resultsArray = $this->db->fetchClass($sqlQuery, $sqlArgs, WordResult::class);
        
        if (count($resultsArray) === 0) {
            return null;
        }
        $wordResult = $resultsArray[0];
        
        if ($joinPatterns) {
            $wordResult->setMatchedPatterns(
                $this->wtpRepo->findByWord($wordResult->getId())
            );
        }
        
        return $wordResult;
    }
}
