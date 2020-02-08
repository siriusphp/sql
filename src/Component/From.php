<?php
declare(strict_types=1);

namespace Sirius\Sql\Component;

use Sirius\Sql\Bindings;

class From extends Component
{
    protected $bindings;

    protected $list = [];

    public function __construct(Bindings $bindings)
    {
        $this->bindings = $bindings;
    }

    public function table(string $ref): void
    {
        $this->list[] = [$ref];
    }

    public function join(string $join, string $ref, string $condition = '', ...$bindingsInline): void
    {
        $condition = ltrim($condition);

        if (
            $condition !== ''
            && strtoupper(substr($condition, 0, 3)) !== 'ON '
            && strtoupper(substr($condition, 0, 6)) !== 'USING '
        ) {
            $condition = 'ON ' . $condition;
        }

        if ( ! empty($bindingsInline)) {
            $condition = $this->bindings->sprintf($condition, ...$bindingsInline);
        }

        end($this->list);
        $end                = key($this->list);
        $this->list[$end][] = "    {$join} {$ref} {$condition}";
    }

    public function build(): string
    {
        if (empty($this->list)) {
            return '';
        }

        $from = [];
        foreach ($this->list as $list) {
            $from[] = array_shift($list) . $this->indent($list);
        }

        return PHP_EOL . 'FROM' . $this->indentCsv($from);
    }
}
