<?php
declare(strict_types=1);

namespace Sirius\Sql\Clause;

use Sirius\Sql\Component\UpdateColumns as UpdateColumnsComponent;

trait UpdateColumns
{
    protected $columns;

    public function column(string $column, ...$value)
    {
        $this->columns->hold($column, ...$value);

        return $this;
    }

    public function columns(array $columns)
    {
        foreach ($columns as $key => $val) {
            if (is_int($key)) {
                $this->column($val);
            } else {
                $this->column($key, $val);
            }
        }

        return $this;
    }

    public function hasColumns(): bool
    {
        return $this->columns->hasAny();
    }

    public function resetColumns()
    {
        $this->columns = new UpdateColumnsComponent($this->bindings, $this->quoter);

        return $this;
    }
}
