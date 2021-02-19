<?php
declare(strict_types = 1);

namespace Tests\Service\DB;

use App\Service\DB\QueryBuilder;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
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
