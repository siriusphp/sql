<?php
declare(strict_types=1);

namespace Sirius\Sql\Clause;

use Sirius\Sql\Component\Limit as DefaultLimit;
use Sirius\Sql\Component\LimitSqlsrv;

trait Limit
{
    /**
     * @var \Sirius\Sql\Component\Limit
     */
    protected $limit;

    public function limit(int $limit)
    {
        $this->limit->setLimit($limit);

        return $this;
    }

    public function offset(int $offset)
    {
        $this->limit->setOffset($offset);

        return $this;
    }

    public function page(int $page)
    {
        $this->limit->setPage($page);

        return $this;
    }

    public function perPage(int $perPage)
    {
        $this->limit->setPerPage($perPage);

        return $this;
    }

    public function resetLimit()
    {
        if ($this->connection->getDriverName() == 'sqlsrv') {
            $this->limit = new LimitSqlsrv();
        } else {
            $this->limit = new DefaultLimit();
        }

        return $this;
    }
}
