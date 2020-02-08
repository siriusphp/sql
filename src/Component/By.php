<?php
declare(strict_types=1);

namespace Sirius\Sql\Component;

class By extends Component
{
    protected $type;

    protected $list = [];

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function expr(string $expr, string ...$exprs): void
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

        return PHP_EOL . $this->type . ' BY' . $this->indentCsv($this->list);
    }
}
