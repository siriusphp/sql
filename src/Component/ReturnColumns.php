<?php
declare(strict_types=1);

namespace Sirius\Sql\Component;

class ReturnColumns extends Component
{
    protected $list = [];

    public function add(string $expr, string ...$exprs): void
    {
        $this->list[] = $expr;
        foreach ($exprs as $expr) {
            $this->list[] = $expr;
        }
    }

    public function build(): string
    {
        if (empty($this->list)) {
            return '';
        }

        return PHP_EOL . 'RETURNING' . $this->indentCsv($this->list);
    }
}
