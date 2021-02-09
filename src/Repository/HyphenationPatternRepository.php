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
     * Truncate patterns table and import new ones
     * @param array<HyphenationPattern> $patterns
     */
    public function import(array $patterns): void
    {
        $truncateSql = sprintf('DELETE FROM `%s`', self::TABLE); // Can't use TRUNCATE as it causes autocommit
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
                $p->isStartPattern() ? 1 : ($p->isEndPattern() ? 2 : 0)
            );
        }
        
        $this->db->beginTransaction();
        if (!$this->db->query($truncateSql) ||
            !$this->db->query($insertSql, $insertArgs)
        ) {
            $this->db->rollbackTransaction();
            throw new \Exception('Error occurred during import. Rolling back.');
        } else
            $this->db->commitTransaction();
    }
}
