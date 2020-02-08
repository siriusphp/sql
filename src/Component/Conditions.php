<?php
declare(strict_types=1);

namespace Sirius\Sql\Component;

use Sirius\Sql\Bindings;
use Sirius\Sql\ConditionsEnum;
use Sirius\Sql\Raw;
use Sirius\Sql\Select;

class Conditions extends Component
{
    protected $bindings;

    protected $type;

    protected $list = [];

    public function __construct(Bindings $bindings, string $type)
    {
        $this->bindings = $bindings;
        $this->type     = $type;
    }

    public function and(string $column, $value = null, $condition = '='): void
    {
        $this->append('AND ', $this->buildExpression($column, $value, $condition));
    }

    public function andSprintf(string $format, ...$bindInline): void
    {
        $this->and($this->bindings->sprintf($format, ...$bindInline), null, null);
    }

    public function or(string $column, $value = null, $condition = '='): void
    {
        $this->append('OR ', $this->buildExpression($column, $value, $condition));
    }

    public function orSprintf(string $format, ...$bindInline): void
    {
        $this->or($this->bindings->sprintf($format, ...$bindInline), null, null);
    }

    public function openGroup($type = 'AND')
    {
        $this->list[] = empty($this->list) ? '(' : $type . ' (';
    }

    public function closeGroup()
    {
        $this->list[] = ')';
    }

    protected function append(string $andor, string $expr): void
    {
        if (empty($this->list)) {
            $andor = '';
        }

        if (end($this->list) == 'AND (' || end($this->list) == 'OR (' || end($this->list) == '(') {
            $andor = '';
        }

        if (empty($this->list) && substr($andor, 0, 4) == 'AND ') {
            $andor = substr($andor, 4);
        }

        if (empty($this->list) && substr($andor, 0, 3) == 'OR ') {
            $andor = substr($andor, 3);
        }

        $this->list[] = $andor . $expr;
    }

    protected function buildExpression(string $column, $value = null, $condition = '='): string
    {
        if ($condition === null) {
            return $column;
        }

        if ($value === null) {
            return $column . ($condition == '=' ? ' IS NULL' : ' IS NOT NULL');
        }

        if ($value instanceof Raw) {
            $value = (string)$value;

            return "{$column} {$condition} {$value}";
        }

        if ($value instanceof Select) {
            $inNotIn = $condition == '=' ? 'IN' : 'NOT IN';

            return "{$column} {$inNotIn} (" . $value->getStatement() . ")";
        }

        if (is_array($value) && in_array($condition, [ConditionsEnum::EQUALS, ConditionsEnum::NOT_EQUALS])) {
            // do this for situations where the value is an empty array because it's the result of a previous
            // query that returned an empty collection (eg: select countries belonging to an empty collection of users)
            if (empty($value)) {
                $value = [-PHP_INT_MAX];
            }
            $bindings = $this->bindings->inline($value);

            $inNotIn = $condition == ConditionsEnum::EQUALS ? 'IN' : 'NOT IN';

            return "{$column} {$inNotIn} {$bindings}";
        }

        switch (strtolower(trim($condition))) {
            case ConditionsEnum::BETWEEN:
                $start = $this->bindings->inline($value[0]);
                $end   = $this->bindings->inline($value[1]);

                return "$column BETWEEN $start AND $end";
            case ConditionsEnum::STARTS_WITH:
                if (substr($value, -1) != '%') {
                    $value .= '%';
                }
                $condition = 'LIKE';
                break;
            case ConditionsEnum::ENDS_WITH:
                if (substr($value, 0, 1) != '%') {
                    $value = '%' . $value;
                }
                $condition = 'LIKE';
                break;
            case ConditionsEnum::CONTAINS:
                if (substr($value, 0, 1) != '%') {
                    $value = '%' . $value;
                }
                if (substr($value, -1) != '%') {
                    $value .= '%';
                }
                $condition = 'LIKE';
                break;
            case ConditionsEnum::DOES_NOT_START_WITH:
                if (substr($value, -1) != '%') {
                    $value .= '%';
                }
                $condition = 'NOT LIKE';
                break;
            case ConditionsEnum::DOES_NOT_END_WITH:
                if (substr($value, 0, 1) != '%') {
                    $value = '%' . $value;
                }
                $condition = 'NOT LIKE';
                break;
            case ConditionsEnum::DOES_NOT_CONTAIN:
                if (substr($value, 0, 1) != '%') {
                    $value = '%' . $value;
                }
                if (substr($value, -1) != '%') {
                    $value .= '%';
                }
                $condition = 'NOT LIKE';
                break;
        }
        $bind = $this->bindings->inline($value);

        return "$column $condition $bind";
    }

    public function build(): string
    {
        if (empty($this->list)) {
            return '';
        }

        return PHP_EOL . $this->type . $this->indent($this->list);
    }
}
