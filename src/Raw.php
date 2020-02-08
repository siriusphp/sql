<?php
declare(strict_types=1);

namespace Sirius\Sql;

class Raw
{
    protected $value;

    public function __construct($value)
    {
        $this->value = (string)$value;
    }

    public function __toString()
    {
        return $this->value;
    }
}
