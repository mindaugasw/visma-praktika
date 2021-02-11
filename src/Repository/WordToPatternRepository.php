<?php

namespace App\Repository;

use App\Entity\HyphenationPattern;
use App\Entity\WordInput;
use App\Entity\WordResult;
use App\Service\DBConnection;

class WordToPatternRepository
{
    const TABLE = 'word_to_pattern';
    
    private DBConnection $db;
    
    public function __construct(DBConnection $db)
    {
        $this->db = $db;
    }
    
    public function truncate(): void
    {
        $truncateSql = sprintf('TRUNCATE `%s`', self::TABLE);
        
        if (!$this->db->query($truncateSql)) {
            throw new \Exception('Error occurred during import');
        }
    }
    
    /**
     * Returns sql query and args array
     * @param array<WordResult> $words
     * @return array [sqlQueryString, argsArray]
     */
    public function buildImportQuery(array $words): array
    {
        $sql = sprintf('INSERT INTO `%s`(`word_id`, `pattern_id`, `position`) VALUES ', self::TABLE);
        $args = [];
        foreach ($words as $word) {
            foreach ($word->getMatchedPatterns() as $pattern) {
                $sql .= '(?,?,?),';
                array_push($args, $word->getId(), $pattern->getId(), $pattern->getPosition());
            }
        }
        $sql = substr($sql, 0, -1); // remove trailing comma
        return [$sql, $args];
    }
    
    public function findByWord(int $wordId): array
    {
        $sql = sprintf(
            'SELECT
                `%1$s`.`position`,
                `%2$s`.*
            FROM
                `%1$s`
            JOIN `%2$s` ON `%1$s`.`pattern_id` = `%2$s`.`id`
            WHERE
                `%1$s`.`word_id` = ?',
            self::TABLE,
            HyphenationPatternRepository::TABLE
        );
        return $this->db->fetchClass($sql, [$wordId], HyphenationPattern::class);
    }
}
