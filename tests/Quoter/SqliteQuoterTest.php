<?php

namespace Sirius\Sql\Tests\Quoter;

use PHPUnit\Framework\TestCase;
use Sirius\Sql\Quoter\SqliteQuoter;

class SqliteQuoterTest extends TestCase
{

    public function test_output()
    {
        $quoter = new SqliteQuoter();
        $this->assertEquals('"column"', $quoter->quoteIdentifier('column'));
    }
}