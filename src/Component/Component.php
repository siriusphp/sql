<?php
declare(strict_types=1);

namespace Sirius\Sql\Component;

abstract class Component
{
    public function indentCsv(array $list): string
    {
        return PHP_EOL . '    '
               . implode(',' . PHP_EOL . '    ', $list);
    }

    public function indent(array $list): string
    {
        if (empty($list)) {
            return '';
        }

        return PHP_EOL . '    '
               . implode(PHP_EOL . '    ', $list);
    }

    abstract public function build(): string;
}
