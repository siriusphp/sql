<?php
declare(strict_types=1);

namespace Sirius\Sql\Component;

class InsertColumns extends ModifyColumns
{
    public function build(): string
    {
        $quotedColumns = [];
        foreach ($this->list as $col => $val) {
            $quotedColumns[] = $this->quoter->quoteIdentifier($col);
        }

        return '('
               . $this->indentCsv($quotedColumns)
               . PHP_EOL . ') VALUES ('
               . $this->indentCsv(array_values($this->list))
               . PHP_EOL . ')';
    }
}
