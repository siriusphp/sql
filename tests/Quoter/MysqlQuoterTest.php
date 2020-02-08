<?php

namespace Sirius\Sql\Tests\Quoter;

use PHPUnit\Framework\TestCase;
use Sirius\Sql\Quoter\MysqlQuoter;

class MysqlQuoterTest extends TestCase
{

    public function test_output()
    {
        $quoter = new MysqlQuoter();
        $this->assertEquals('`column`', $quoter->quoteIdentifier('column'));
    }
}