<?php

namespace App\Repository;

use App\Entity\HyphenationPattern;
use App\Entity\WordResult;
use App\Service\DB\DBConnection;
use App\Service\DB\QueryBuilder;
use Exception;

class WordToPatternRepository
{
    public const TABLE = 'word_to_pattern';
    
    private DBConnection $db;
    
    public function __construct(DBConnection $db)
    {
        $this->db = $db;
    }
    
    public function truncate(): void
    {
        $truncateSql = (new QueryBuilder())
            ->truncate(self::TABLE)
            ->getQuery();
        
        if (!$this->db->query($truncateSql)) {
            throw new Exception('Error occurred during import');
        }
    }
    
    /**
     * Returns sql query and args array
     *
     * @param  array<WordResult> $words
     * @return array [sqlQueryString, argsArray]
     */
    public function buildImportQuery(array $words): array
    {
        $args = [];
        $patternsCount = 0;
        foreach ($words as $word) {
            foreach ($word->getMatchedPatterns() as $pattern) {
                //$sql .= '(?,?,?),';
                $patternsCount++;
                array_push($args, $word->getId(), $pattern->getId(), $pattern->getPosition());
            }
        }
        
        $sql = (new QueryBuilder())
            ->insertInto(self::TABLE, ['word_id', 'pattern_id', 'position'])
            ->values('?,?,?', $patternsCount)
            ->getQuery();
        
        if ($patternsCount !== 0) {
            return [$sql, $args];
        } else {
            return [null, []]; // no matched patterns
        }
    }
    
    public function findByWord(int $wordId): array
    {
        $sql = (new QueryBuilder())
            ->select(self::TABLE . '.position', HyphenationPatternRepository::TABLE . '.*')
            ->from(self::TABLE)
            ->joinOn(
                HyphenationPatternRepository::TABLE,
                sprintf(
                    '%s.pattern_id=%s.id',
                    self::TABLE,
                    HyphenationPatternRepository::TABLE
                )
            )
            ->where(self::TABLE . '.word_id=?')
            ->getQuery();
        
        return $this->db->fetchClass($sql, [$wordId], HyphenationPattern::class);
    }
}
