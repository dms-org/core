<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Query;

use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Query\Expression\Expr;

/**
 * The db update query class.
 *
 * This should update the row values matched by the query criteria.
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
    public function getColumnSetMap() : array
    {
        return $this->columnSetMap;
    }

    /**
     * @param string     $column
     * @param Expr $expression
     *
     * @return static
     */
    public function set(string $column, Expr $expression)
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