<?php
declare(strict_types=1);

namespace Sirius\Sql\Clause;

use Sirius\Sql\Component\Conditions;

trait Having
{
    /**
     * @var Conditions
     */
    protected $having;

    public function having(string $condition, ...$bindInline)
    {
        $this->having->andSprintf($condition, ...$bindInline);

        return $this;
    }

    public function orHaving(string $condition, ...$bindInline)
    {
        $this->having->orSprintf($condition, ...$bindInline);

        return $this;
    }

    public function resetHaving()
    {
        $this->having = new Conditions($this->bindings, 'HAVING');

        return $this;
    }
}
