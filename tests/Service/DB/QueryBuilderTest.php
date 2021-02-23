<?php

declare(strict_types=1);

namespace Tests\Service\DB;

use App\Service\DB\QueryBuilder;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    /**
     * Remove more than 1 consecutive space
     * Should be used with preg_replace to replace with nothing
     */
    private const REGEX_SPACE_REMOVE = '/ {2,}/';
    
    public function testSelect(): void
    {
        $sql = (new QueryBuilder())
            ->select('word.input', 'word.result')
            ->from('word')
            ->where('input=?')
            ->getQuery();
        
        $expectedSql = 'SELECT word.input, word.result FROM word WHERE input=?';
        
        $sql = $this->removeExtraSpaces($sql);
        $expectedSql = $this->removeExtraSpaces($expectedSql);
        
        $this->assertSame($expectedSql, $sql);
    }
    
    public function testJoin(): void
    {
        $sql = (new QueryBuilder())
            ->select('word_to_pattern.position', 'hyphenation_pattern.*')
            ->from('word_to_pattern')
            ->joinOn('hyphenation_pattern', 'word_to_pattern.pattern_id=hyphenation_pattern.id')
            ->where('word_to_pattern.word_id=?')
            ->getQuery();
        
        $expectedSql =
            'SELECT word_to_pattern.position, hyphenation_pattern.*  FROM word_to_pattern '
            .' JOIN hyphenation_pattern ON word_to_pattern.pattern_id=hyphenation_pattern.id '
            .' WHERE word_to_pattern.word_id=?';
    
        $sql = $this->removeExtraSpaces($sql);
        $expectedSql = $this->removeExtraSpaces($expectedSql);
    
        $this->assertSame($expectedSql, $sql);
    }
    
    public function testInsert(): void
    {
        $sql = (new QueryBuilder())
            ->insertInto(
                'hyphenation_pattern',
                ['pattern', 'patternNoDot', 'patternNoNumbers', 'patternText', 'patternType']
            )
            ->values('?,?,?,?,?', 3)
            ->getQuery();
    
        $expectedSql =
             'INSERT INTO hyphenation_pattern '
            .'(pattern, patternNoDot, patternNoNumbers, patternText, patternType) '
            .' VALUES (?,?,?,?,?), (?,?,?,?,?), (?,?,?,?,?)';
    
        $sql = $this->removeExtraSpaces($sql);
        $expectedSql = $this->removeExtraSpaces($expectedSql);
    
        $this->assertSame($expectedSql, $sql);
    }
    
    /**
     * All QueryBuilder methods add extra spaces around all statements.
     * Remove any extra spaces, leaving no more than 1 consecutive space
     * @param string $text
     * @return string
     */
    private function removeExtraSpaces(string $text): string
    {
        return preg_replace(self::REGEX_SPACE_REMOVE, ' ', trim($text));
    }
}
