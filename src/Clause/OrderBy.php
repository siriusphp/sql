<?php
declare(strict_types=1);

namespace Sirius\Sql\Clause;

use Sirius\Sql\Component\By;

trait OrderBy
{
    protected $orderBy;

    public function orderBy(string $expr, string ...$exprs)
    {
        $this->orderBy->expr($expr, ...$exprs);

        return $this;
    }

    public function resetOrderBy()
    {
        $this->orderBy = new By('ORDER');

        return $this;
    }
}
