<?php
declare(strict_types=1);

namespace Sirius\Sql;

class Update extends Query
{
    use Clause\UpdateColumns;
    use Clause\Where;
    use Clause\OrderBy;
    use Clause\Limit;
    use Clause\Returning;

    protected $table;

    public function table(string $table)
    {
        $this->table = $table;

        return $this;
    }

    public function getStatement(): string
    {
        return 'UPDATE'
               . $this->flags->build()
               . ' ' . $this->table
               . $this->columns->build()
               . $this->where->build()
               . $this->returning->build();
    }
}
