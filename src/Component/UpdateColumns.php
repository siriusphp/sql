<?php
declare(strict_types=1);

namespace Sirius\Sql\Component;

class UpdateColumns extends ModifyColumns
{
    public function build(): string
    {
        $values = array();
        foreach ($this->list as $column => $value) {
            $quotedColumn = $this->quoter->quoteIdentifier($column);
            $values[]     = "{$quotedColumn} = {$value}";
        }

        return PHP_EOL . 'SET' . $this->indentCsv($values);
    }
}
