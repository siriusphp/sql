<?php

namespace Sirius\Sql\Tests;

use PHPUnit\Framework\TestCase;
use Sirius\Sql\Insert;

class InsertTest extends QueryTestCase
{
    /**
     * @return Insert
     */
    public function newInsert()
    {
        return new Insert($this->getConnectionMock());
    }

    public function test_statement_and_bindings()
    {
        /** @var Insert $insert */
        $insert = $this->newInsert()
                       ->ignore()
                       ->into('posts')
                       ->columns(['title' => 'abc', 'content' => 'xyz', 'publish_date'])
                       ->returning('title', 'content')
                       ->returning('publish_date');

        $statement = <<<SQL
    INSERT IGNORE INTO posts 
    (`title`, `content`, `publish_date`)
    VALUES
    (:title, :content, :publish_date)
    RETURNING
    title, content, publish_date
SQL;

        $this->assertSameStatement($statement, $insert->getStatement());

        $bindings = $insert->getBindValues();
        $this->assertTrue(isset($bindings['title']));
        $this->assertTrue(isset($bindings['content']));
        $this->assertFalse(isset($bindings['publish_date']));

        $this->assertTrue($insert->hasColumns());

        $this->assertEquals('id-1', $insert->getLastInsertId('id'));
    }
}