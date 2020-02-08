<?php

namespace Sirius\Sql\Tests\Quoter;

use PHPUnit\Framework\TestCase;
use Sirius\Sql\Quoter\PgsqlQuoter;

class PgsqlQuoterTest extends TestCase
{

    public function test_output()
    {
        $quoter = new PgsqlQuoter();
        $this->assertEquals('"column"', $quoter->quoteIdentifier('column'));
    }
}