<?php
declare(strict_types=1);

namespace Sirius\Sql;

class Delete extends Query
{
    use Clause\Where;
    use Clause\OrderBy;
    use Clause\Limit;
    use Clause\Returning;

    protected $table = '';

    public function from(string $table)
    {
        $this->table = $table;

        return $this;
    }

    public function getStatement(): string
    {
        return 'DELETE'
               . $this->flags->build()
               . ' FROM ' . $this->table
               . $this->where->build()
               . $this->returning->build();
    }
}
