<?php
declare(strict_types=1);

namespace Sirius\Sql;

use Atlas\Pdo\Connection;

class QueryFactory
{

    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function newSelect()
    {
        return new Select($this->connection);
    }

    public function newInsert()
    {
        return new Insert($this->connection);
    }

    public function newUpdate()
    {
        return new Update($this->connection);
    }

    public function newDelete()
    {
        return new Delete($this->connection);
    }
}
