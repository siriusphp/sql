<?php
declare(strict_types=1);

namespace Sirius\Sql\Clause;

use Sirius\Sql\Component\Conditions;

trait Where
{
    /**
     * @var Conditions
     */
    protected $where;

    /**
     * Adds a column based condition condition
     *
     * @param string $column
     * @param null $value
     * @param string $condition
     *
     * @return $this
     */
    public function where($column, $value = null, $condition = '=')
    {
        if (is_array($column) && is_array($value)) {
            return $this->whereMultiple($column, $value, $condition);
        }
        if (count(func_get_args()) == 1) {
            $this->where->and($column, null, null);
        } else {
            $this->where->and($column, $value, $condition);
        }

        return $this;
    }

    public function whereSprintf(string $format, ...$bindInline)
    {
        $this->where->andSprintf($format, ...$bindInline);

        return $this;
    }

    /**
     * Helper method for situation when you need to query by primary key
     * which consists of multiple columns. Only works for `=` and `IN`
     *
     * @param array $columns
     * @param array $values
     * @param $condition
     *
     * @return $this
     */
    public function whereMultiple(array $columns, array $values, $condition)
    {
        $condition = strtoupper($condition);
        if (! in_array($condition, ['=', 'IN'])) {
            throw new \InvalidArgumentException('For `whereMultiple` the condition `' . $condition . '` is not allowed');
        }

        if (! is_array($values[0])) {
            $values = [$values];
        }

        $this->whereStartSet();
        foreach ($values as $value) {
            // match ALL of the column-value pairs
            $this->orWhereStartSet();
            foreach ($columns as $k => $column) {
                $this->where($column, $value[$k]);
            }
            $this->endSet();
        }

        $this->endSet();

        return $this;
    }

    public function orWhere(string $column, $value = null, $condition = '=')
    {
        if (count(func_get_args()) == 1) {
            $this->where->or($column, null, null);
        } else {
            $this->where->or($column, $value, $condition);
        }

        return $this;
    }

    public function orWhereSprintf(string $format, ...$bindInline)
    {
        $this->where->orSprintf($format, ...$bindInline);

        return $this;
    }

    public function whereAll(array $columnsValues, $isSet = true)
    {
        if ($isSet) {
            $this->whereStartSet();
        }

        foreach ($columnsValues as $key => $val) {
            if (is_numeric($key)) {
                $this->where($val);
            } elseif ($val === null) {
                $this->where("{$key} IS NULL");
            } else {
                $this->where("{$key}", $val);
            }
        }


        if ($isSet) {
            $this->endSet();
        }

        return $this;
    }

    public function whereStartSet()
    {
        $this->where->openGroup();

        return $this;
    }

    public function orWhereStartSet()
    {
        $this->where->openGroup('OR');

        return $this;
    }

    public function endSet()
    {
        $this->where->closeGroup();

        return $this;

        return $this;
    }

    public function groupCurrentWhere()
    {
        $this->where->groupCurrent();

        return $this;
    }

    public function resetWhere()
    {
        $this->where = new Conditions($this->bindings, 'WHERE');

        return $this;
    }
}
