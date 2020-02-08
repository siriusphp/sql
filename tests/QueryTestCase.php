<?php

namespace Sirius\Sql\Tests;

use Atlas\Pdo\Connection;
use PHPUnit\Framework\TestCase;

class QueryTestCase extends TestCase
{
    public function getConnectionMock()
    {
        $conn = \Mockery::mock(Connection::class);
        $conn->shouldReceive('getDriverName')->andReturn('mysql');
        $conn->shouldReceive('lastInsertId')->with('id')->andReturn('id-1');
        $conn->shouldReceive('fetchAll')->andReturn([true]);

        return $conn;
    }

    protected function assertSameStatement($expect, $actual)
    {
        $this->assertSame($this->removeWhiteSpace($expect), $this->removeWhiteSpace($actual));
    }

    protected function removeWhiteSpace($str)
    {
        $str = trim($str);
        $str = preg_replace('/^[ \t]*/m', '', $str);
        $str = preg_replace('/[ \t]*$/m', '', $str);
        $str = preg_replace('/[ ]{2,}/m', ' ', $str);
        $str = preg_replace('/[\r\n|\n|\r]+/', ' ', $str);
        $str = str_replace('( ', '(', $str);
        $str = str_replace(' )', ')', $str);
        return $str;
    }
}