<?php
declare(strict_types=1);

namespace Sirius\Sql;

use Atlas\Pdo\Connection;
use Sirius\Sql\Component\Flags;

abstract class Query
{
    protected $bindings;

    protected $connection;

    protected $flags;

    protected $quoter;

    protected $indent = '';

    public function __construct(Connection $connection, Bindings $bindings = null, $indent = '')
    {
        $this->connection = $connection;
        if (! $bindings) {
            $bindings = new Bindings();
        }
        $this->bindings = $bindings;

        $quoter       = 'Sirius\Sql\\Quoter\\'
                        . ucfirst($this->connection->getDriverName())
                        . 'Quoter';
        $this->quoter = new $quoter();

        $this->indent = $indent;

        $this->reset();
    }

    /**
     * Perform the query at it's current state (conditions and bind values)
     *
     * @return \PDOStatement
     */
    public function perform()
    {
        return $this->connection->perform(
            $this->getStatement(),
            $this->getBindValues()
        );
    }

    /**
     * Creates a numbered binding placeholder to be added to the statement
     * like :__102__, :__3__ and adds the value to the list of bind values
     *
     * @param $value
     * @param int $type
     *
     * @return string
     */
    public function bindInline($value, int $type = -1)
    {
        return $this->bindings->inline($value, $type);
    }

    /**
     * Creates a string where %s is replaced by numbered binding placeholders
     * and adds the values to the list of bind values
     *
     * @param string $format
     * @param mixed ...$values
     *
     * @return string
     * @example $format = "date between %s and %s", returns "date between :__4__ and :__5__"
     *
     */
    public function bindSprintf(string $format, ...$values): string
    {
        return $this->bindings->sprintf($format, ...$values);
    }

    /**
     * Creates a named binding placedholder and adds the value to the bind values
     *
     * @param string $key
     * @param $value
     * @param int $type
     *
     * @return $this
     * @example $key = 'title', returns ':title'
     *
     */
    public function bindValue(string $key, $value, int $type = -1)
    {
        $this->bindings->value($key, $value, $type);

        return $this;
    }

    /**
     * Binds a set of key-value pairs to the statement
     *
     * @param array $values
     *
     * @return $this
     */
    public function bindValues(array $values)
    {
        $this->bindings->values($values);

        return $this;
    }

    /**
     * Returns a RAW value that will be used as-is in the statement
     *
     * @param $value
     *
     * @return Raw
     */
    public function raw($value): Raw
    {
        return new Raw($value);
    }

    public function getBindValues(): array
    {
        return $this->bindings->getArrayCopy();
    }

    /**
     * Sets a statement flag like IGNORE for INSERT or DISTINCT for SELECT
     *
     * @param string $flag
     * @param bool $enable
     */
    public function setFlag(string $flag, bool $enable = true): void
    {
        $this->flags->set($flag, $enable);
    }

    /**
     * Resets the query (conditions, columns etc)
     * @return $this
     */
    public function reset()
    {
        foreach (get_class_methods($this) as $method) {
            if (substr($method, 0, 5) == 'reset' && $method != 'reset') {
                $this->$method();
            }
        }

        return $this;
    }

    public function resetFlags()
    {
        $this->flags = new Flags();

        return $this;
    }

    public function quoteIdentifier(string $name): string
    {
        return $this->quoter->quoteIdentifier($name);
    }

    /**
     * Returns the statement to be executed on the connection
     * @return string
     */
    abstract public function getStatement(): string;

    /**
     * Returns a compiled version of the query.
     * To be used only for debugging purposes.
     * All lines are prepended with # so you don't accidentally run it
     *
     * @return string
     */
    public function __toString()
    {
        $bindings = $this->getBindValues();
        $sql      = $this->getStatement();
        foreach ($bindings as $k => $v) {
            $value = $v[0];
            if ($v[1] == \PDO::PARAM_STR) {
                $value = "'" . $value . "'";
            }
            $sql = str_replace(':' . $k, $value, $sql);
        }
        $sql = preg_replace('/:[0-9a-z_]+/', 'NULL', $sql);

        return "# " . str_replace(PHP_EOL, PHP_EOL . '# ', $sql);
    }
}
