<?php
declare(strict_types=1);

namespace Sirius\Sql;

class Insert extends Query
{
    use Clause\InsertColumns;
    use Clause\Returning;

    protected $table = '';

    /**
     * @param string $table
     *
     * @return $this
     */
    public function into(string $table)
    {
        $this->table = $table;

        return $this;
    }

    public function ignore(bool $enable = true)
    {
        $this->setFlag('IGNORE', $enable);

        return $this;
    }


    public function getStatement(): string
    {
        return 'INSERT'
               . $this->flags->build()
               . " INTO {$this->table} "
               . $this->columns->build()
               . $this->returning->build();
    }

    /**
     * @param string|null $name
     *
     * @return string
     */
    public function getLastInsertId(string $name = null)
    {
        return $this->connection->lastInsertId($name);
    }
}
