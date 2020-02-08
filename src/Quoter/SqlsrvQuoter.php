<?php
declare(strict_types=1);

namespace Sirius\Sql\Quoter;

class SqlsrvQuoter extends Quoter
{
    public function quoteIdentifier(string $name): string
    {
        return "[{$name}]";
    }
}
