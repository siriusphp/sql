<?php
declare(strict_types=1);

namespace Sirius\Sql\Component;

class Flags extends Component
{
    protected $list = [];

    public function set(string $flag, bool $enable = true): void
    {
        if ($enable) {
            $this->list[$flag] = true;
        } else {
            unset($this->list[$flag]);
        }
    }

    public function get(): array
    {
        return array_keys($this->list);
    }

    public function build(): string
    {
        if (empty($this->list)) {
            return '';
        }

        return ' ' . implode(' ', $this->get());
    }
}
