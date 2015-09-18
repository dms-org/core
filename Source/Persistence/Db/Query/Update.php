<?php

namespace Iddigital\Cms\Core\Persistence\Db\Query;

use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;

/**
 * The db update query class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Update extends Query
{
    /**
     * @var Expr[]
     */
    private $columnSetMap = [];

    /**
     * @return Expr[]
     */
    public function getColumnSetMap()
    {
        return $this->columnSetMap;
    }

    /**
     * @param string     $column
     * @param Expr $expression
     *
     * @return static
     */
    public function set($column, Expr $expression)
    {
        $this->columnSetMap[$column] = $expression;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function executeOn(IConnection $connection)
    {
        $connection->update($this);
    }
}