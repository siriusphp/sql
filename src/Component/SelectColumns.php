<?php
declare(strict_types=1);

namespace Sirius\Sql\Component;

class SelectColumns extends Component
{
    protected $list = [];

    public function add(string $expr, string ...$exprs): void
    {
        $this->list[] = $expr;
        foreach ($exprs as $expr) {
            $this->list[] = $expr;
        }
    }

    public function hasAny(): bool
    {
        return ! empty($this->list);
    }

    public function build(): string
    {
        if (empty($this->list)) {
            return $this->indentCsv(['*']);
        }
        return $this->indentCsv($this->list);
    }
}
