<?php

namespace Sirius\Sql\Tests;

use PHPUnit\Framework\TestCase;
use Sirius\Sql\ConditionsEnum;
use Sirius\Sql\Insert;
use Sirius\Sql\Update;

class UpdateTest extends QueryTestCase
{
    /**
     * @return Update
     */
    public function newUpdate()
    {
        return new Update($this->getConnectionMock());
    }

    public function test_statement_and_bindings()
    {
        $update = $this->newUpdate();
        $update->table('posts')
               ->columns(['title', 'content' => 'xyz', 'publish_date' => $update->raw('NOW()')])
               ->where('id', [10, 11])
               ->where('title', 'abc', ConditionsEnum::STARTS_WITH);

        $statement = <<<SQL
    UPDATE posts 
    SET `title` = :title, `content` = :content, `publish_date` = NOW()
    WHERE id IN (:__1__, :__2__) AND title LIKE :__3__
SQL;

        $this->assertSameStatement($statement, $update->getStatement());

        $bindings = $update->getBindValues();
        $this->assertsame([
            'content' => ['xyz', \PDO::PARAM_STR],
            '__1__'   => [10, \PDO::PARAM_INT],
            '__2__'   => [11, \PDO::PARAM_INT],
            '__3__'   => ['abc%', \PDO::PARAM_STR],
        ], $update->getBindValues());

        $this->assertTrue($update->hasColumns());

        $expectedSql = <<<SQL
# UPDATE posts
# SET
#     `title` = NULL,
#     `content` = 'xyz',
#     `publish_date` = NOW()
# WHERE
#     id IN (10, 11)
#     AND title LIKE 'abc%'
SQL;

        $this->assertEquals(str_replace("\r", "", $expectedSql), str_replace("\r", "", $update->__toString()));
    }
}