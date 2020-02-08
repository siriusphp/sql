<?php

namespace Sirius\Sql\Tests\Quoter;

use PHPUnit\Framework\TestCase;
use Sirius\Sql\Quoter\SqlsrvQuoter;

class SqlsrvQuoterTest extends TestCase
{

    public function test_output()
    {
        $quoter = new SqlsrvQuoter();
        $this->assertEquals('[column]', $quoter->quoteIdentifier('column'));
    }
}