<?php
declare(strict_types=1);

namespace Sirius\Sql\Clause;

use Sirius\Sql\Component\By;

trait GroupBy
{
    /**
     * @var By
     */
    protected $groupBy;

    public function resetGroupBy()
    {
        $this->groupBy = new By('GROUP');

        return $this;
    }

    public function groupBy(string $expr, string ...$exprs)
    {
        $this->groupBy->expr($expr, ...$exprs);

        return $this;
    }
}
