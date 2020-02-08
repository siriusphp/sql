<?php
declare(strict_types=1);

namespace Sirius\Sql\Quoter;

abstract class Quoter
{
    abstract public function quoteIdentifier(string $name): string;
}
