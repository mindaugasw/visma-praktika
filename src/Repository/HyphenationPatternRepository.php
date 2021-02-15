<?php

namespace App\Repository;

use App\Entity\HyphenationPattern;
use App\Service\DBConnection;

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
        $sql = sprintf('SELECT * FROM `%s`', self::TABLE);
        return $this->db->fetchClass($sql, [], HyphenationPattern::class);
    }
    
    public function findOne(int $patternId): ?HyphenationPattern
    {
        $sql = sprintf('SELECT * FROM `%s` WHERE `id`=?', self::TABLE);
        $results = $this->db->fetchClass($sql, [$patternId], HyphenationPattern::class);
        
        if (count($results) === 0) {
            return null;
        } else {
            return $results[0];
        }
    }
    
    public function getPaginated(int $limit, int $offset): array
    {
        $sql = sprintf('SELECT * FROM `%s` LIMIT %d OFFSET %d', self::TABLE, $limit, $offset);
        // TODO use proper mysql params with bindParam(PDO::PARAM_INT)
        
        return $this->db->fetchClass($sql, [], HyphenationPattern::class);
    }
    
    /**
     * Truncate patterns table and import new ones
     * @param array<HyphenationPattern> $patterns
     * @return void
     */
    public function import(array $patterns): void
    {
        $insertSql = sprintf('INSERT INTO `%s`(`pattern`, `patternNoDot`, `patternNoNumbers`, `patternText`, `patternType`) VALUES ', self::TABLE);
        $insertArgs = [];
        
        for ($i = 0, $len = count($patterns); $i < $len; $i++) {
            $p = $patterns[$i];
            $insertSql .= '(?,?,?,?,?)';
            
            if ($i !== $len - 1)
                $insertSql .= ',';
            
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
        
        if (!$this->db->query($insertSql, $insertArgs))
            throw new \Exception('Error occurred during import');
    }
    
    public function truncate(): void
    {
        $truncateSql = sprintf('DELETE FROM `%s`', self::TABLE); // can't TRUNCATE cause of FK
        if (!$this->db->query($truncateSql))
            throw new \Exception();
    }
   
}
