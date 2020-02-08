<?php
declare(strict_types=1);

namespace Sirius\Sql\Clause;

use Sirius\Sql\Component\SelectColumns as SelectColumnsComponent;

trait SelectColumns
{
    protected $columns;

    public function columns(string $expr, string ...$exprs)
    {
        $this->columns->add($expr, ...$exprs);

        return $this;
    }

    public function resetColumns()
    {
        $this->columns = new SelectColumnsComponent();

        return $this;
    }

    public function hasColumns()
    {
        return $this->columns->hasAny();
    }
}
