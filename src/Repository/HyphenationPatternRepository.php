<?php

namespace App\Repository;

use App\Entity\HyphenationPattern;
use App\Service\DB\DBConnection;
use App\Service\DB\QueryBuilder;

class HyphenationPatternRepository
{
    const TABLE = 'hyphenation_pattern';
    
    private DBConnection $db;
    
    public function __construct(DBConnection $db)
    {
        $this->db = $db;
    }
    
    /**
     * @return array<HyphenationPattern>
     */
    public function getAll(): array
    {
        $sql = (new QueryBuilder())
            ->select('*')
            ->from(self::TABLE)
            ->getQuery();
        
        return $this->db->fetchClass($sql, [], HyphenationPattern::class);
    }
    
    public function findOne(int $patternId): ?HyphenationPattern
    {
        $sql = (new QueryBuilder())
            ->select('*')
            ->from(self::TABLE)
            ->where('id=?')
            ->getQuery();
        
        $results = $this->db->fetchClass($sql, [$patternId], HyphenationPattern::class);
        
        if (count($results) === 0) {
            return null;
        } else {
            return $results[0];
        }
    }
    
    public function getPaginated(int $limit, int $offset): array
    {
        $sql = (new QueryBuilder())
            ->select('*')
            ->from(self::TABLE)
            ->limitOffset($limit, $offset)
            ->getQuery();
        
        return $this->db->fetchClass($sql, [], HyphenationPattern::class);
    }
    
    /**
     * Truncate patterns table and import new ones
     * @param array<HyphenationPattern> $patterns
     * @return void
     */
    public function import(array $patterns): void
    {
        $insertArgs = [];
        
        for ($i = 0, $len = count($patterns); $i < $len; $i++) {
            $p = $patterns[$i];
            
            array_push(
                $insertArgs,
                $p->getPattern(),
                $p->getPatternNoDot(),
                $p->getPatternNoNumbers(),
                $p->getPatternText(),
                $p->isStartPattern() ?
                    HyphenationPattern::TYPE_START : 
                    ($p->isEndPattern() ? 
                        HyphenationPattern::TYPE_END : 
                        HyphenationPattern::TYPE_REGULAR)
            );
        }
    
        $insertSql = (new QueryBuilder())
            ->insertInto(self::TABLE, ['pattern', 'patternNoDot', 'patternNoNumbers', 'patternText', 'patternType'])
            ->values('?,?,?,?,?', count($patterns))
            ->getQuery();
        
        if (!$this->db->query($insertSql, $insertArgs))
            throw new \Exception('Error occurred during import');
    }
    
    public function truncate(): void
    {
        $truncateSql = (new QueryBuilder())
            ->delete() // can't TRUNCATE cause of FKs
            ->from(self::TABLE)
            ->getQuery();
        
        if (!$this->db->query($truncateSql))
            throw new \Exception();
    }
   
}
