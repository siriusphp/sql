<?php
declare(strict_types=1);

namespace Sirius\Sql;

use BadMethodCallException;
use Generator;
use Sirius\Sql\Component\From;

/**
 * @method array fetchAll()
 * @method int fetchAffected()
 * @method array fetchColumn(int $column = 0)
 * @method array fetchGroup(int $style = PDO::FETCH_COLUMN)
 * @method array fetchKeyPair()
 * @method mixed fetchObject(string $class = 'stdClass', array $args = [])
 * @method array fetchObjects(string $class = 'stdClass', array $args = [])
 * @method array|null fetchOne()
 * @method array fetchUnique()
 * @method mixed fetchValue(int $column = 0)
 * @method Generator yieldAll()
 * @method Generator yieldColumn(int $column = 0)
 * @method Generator yieldKeyPair()
 * @method Generator yieldObjects(string $class = 'stdClass', array $args = [])
 * @method Generator yieldUnique()
 */
class Select extends Query
{
    use Clause\SelectColumns;
    use Clause\Where;
    use Clause\GroupBy;
    use Clause\Having;
    use Clause\OrderBy;
    use Clause\Limit;

    protected $as;
    /**
     * @var From
     */
    protected $from;
    protected $unions = [];
    protected $forUpdate = false;

    public function __clone()
    {
        $vars = get_object_vars($this);
        unset($vars['bindings']);
        foreach ($vars as $name => $prop) {
            if (is_object($prop)) {
                $this->$name = clone $prop;
            }
        }
    }

    public function __call(string $method, array $params)
    {
        $prefix = substr($method, 0, 5);
        if ($prefix == 'fetch' || $prefix == 'yield') {
            return $this->connection->$method(
                $this->getStatement(),
                $this->getBindValues(),
                ...$params
            );
        }

        throw new BadMethodCallException($method);
    }

    public function forUpdate(bool $enable = true)
    {
        $this->forUpdate = $enable;

        return $this;
    }

    /**
     * Makes the query select distinct rows
     *
     * @param bool $enable
     *
     * @return $this
     */
    public function distinct(bool $enable = true)
    {
        $this->setFlag('DISTINCT', $enable);

        return $this;
    }

    public function from(string $ref)
    {
        $this->from->table($ref);

        return $this;
    }

    /**
     * Joins a table/subselect based on a condition
     * The condition can have named bindable placeholders (eg: :some_column) to be bound later
     * OR sprintf-like placholders (ie: %s) that can be bound immediately with $bindInline
     *
     * @param string $join
     * @param string $ref
     * @param string $condition
     * @param mixed ...$bindInline
     *
     * @return $this
     */
    public function join(string $join, $ref, string $condition = '', ...$bindInline)
    {
        $join = strtoupper(trim($join));
        if (substr($join, -4) != 'JOIN') {
            $join .= ' JOIN';
        }

        if ($ref instanceof Select) {
            $ref = $ref->getStatement();
        }

        $this->from->join($join, $ref, $condition, ...$bindInline);

        return $this;
    }

    public function union()
    {
        $this->unions[] = $this->getCurrentStatement(
            PHP_EOL . 'UNION' . PHP_EOL
        );
        $this->reset();

        return $this;
    }

    public function unionAll()
    {
        $this->unions[] = $this->getCurrentStatement(
            PHP_EOL . 'UNION ALL' . PHP_EOL
        );
        $this->reset();

        return $this;
    }

    public function as(string $as)
    {
        $this->as = $as;

        return $this;
    }

    public function resetFrom()
    {
        $this->from = new From($this->bindings);

        return $this;
    }

    public function resetAs()
    {
        $this->as = null;

        return $this;
    }

    public function subSelect(): Select
    {
        return new Select($this->connection, $this->bindings, $this->indent . '    ');
    }

    public function getStatement(): string
    {
        return implode('', $this->unions) . $this->getCurrentStatement();
    }

    protected function getCurrentStatement(string $suffix = ''): string
    {
        $stm = 'SELECT'
               . $this->flags->build()
               . $this->limit->buildEarly()
               . $this->columns->build()
               . $this->from->build()
               . $this->where->build()
               . $this->groupBy->build()
               . $this->having->build()
               . $this->orderBy->build()
               . $this->limit->build()
               . ($this->forUpdate ? PHP_EOL . 'FOR UPDATE' : '');

        if ($this->as !== null) {
            $stm = "(" . PHP_EOL . $stm . PHP_EOL . ") AS {$this->as}";
        }

        if ($this->indent) {
            $stm = $this->indent . str_replace(PHP_EOL, PHP_EOL . $this->indent, $stm);
        }

        return $stm . $suffix;
    }
}
