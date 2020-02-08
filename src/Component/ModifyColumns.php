<?php
declare(strict_types=1);

namespace Sirius\Sql\Component;

use Sirius\Sql\Bindings;
use Sirius\Sql\Quoter\Quoter;
use Sirius\Sql\Raw;

abstract class ModifyColumns extends Component
{
    protected $bindings;

    protected $list = [];

    protected $quoter;

    public function __construct(Bindings $bindings, Quoter $quoter)
    {
        $this->bindings = $bindings;
        $this->quoter   = $quoter;
    }

    public function hasAny(): bool
    {
        return ! empty($this->list);
    }

    public function hold(string $column, ...$value): void
    {
        if (! empty($value) && $value[0] instanceof Raw) {
            $this->list[$column] = (string)$value[0];

            return;
        }

        $this->list[$column] = ":{$column}";
        if (! empty($value)) {
            $this->bindings->value($column, ...$value);
        }
    }
}
