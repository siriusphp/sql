<?php
declare(strict_types=1);

namespace Sirius\Sql\Clause;

use Sirius\Sql\Component\ReturnColumns;

trait Returning
{
    protected $returning;

    public function returning(string $expr, string ...$exprs)
    {
        $this->returning->add($expr, ...$exprs);

        return $this;
    }

    public function resetReturning()
    {
        $this->returning = new ReturnColumns();

        return $this;
    }
}
