<?php

namespace Sirius\Sql\Tests;

use PHPUnit\Framework\TestCase;
use Sirius\Sql\Delete;
use Sirius\Sql\Insert;

class DeleteTest extends QueryTestCase
{
    /**
     * @return Delete
     */
    public function newDelete()
    {
        return new Delete($this->getConnectionMock());
    }

    public function test_statement_and_bindings()
    {
        /** @var Insert $insert */
        $insert = $this->newDelete()
                       ->from('posts')
                       ->where('id', 10);

        $this->assertSameStatement('DELETE FROM posts WHERE id = :__1__', $insert->getStatement());

    }
}