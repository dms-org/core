<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Query;

use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Table;

/**
 * The db resequence column query class.
 *
 * This will fill a column with (1-based) incrementing integers ordered by
 * to the values already in that column.
 *
 * This can be used to remove duplicates and gaps within the existing values.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ResequenceOrderIndexColumn implements IQuery
{
    /**
     * @var Table
     */
    private $table;

    /**
     * @var Column|null
     */
    private $groupingColumn;

    /**
     * @var Column
     */
    private $column;

    /**
     * @var Expr|null
     */
    private $whereCondition;

    /**
     * ResequenceOrderIndexColumn constructor.
     *
     * @param Table       $table
     * @param string      $columnName
     * @param string|null $groupingColumnName
     * @param Expr|null   $whereCondition
     *
     * @throws \Dms\Core\Exception\InvalidArgumentException
     */
    public function __construct(Table $table, string $columnName, string $groupingColumnName = null, Expr $whereCondition = null)
    {
        $this->table          = $table;
        $this->column         = $table->getColumn($columnName);
        $this->groupingColumn = $groupingColumnName ? $table->getColumn($groupingColumnName) : null;
        $this->whereCondition = $whereCondition;
    }

    /**
     * @return Table
     */
    public function getTable() : Table
    {
        return $this->table;
    }

    /**
     * @return Column
     */
    public function getColumn() : Column
    {
        return $this->column;
    }

    /**
     * @return bool
     */
    public function hasGroupingColumn() : bool
    {
        return $this->groupingColumn !== null;
    }

    /**
     * @return Column|null
     */
    public function getGroupingColumn()
    {
        return $this->groupingColumn;
    }

    /**
     * @return bool
     */
    public function hasWhereCondition() : bool
    {
        return $this->whereCondition !== null;
    }

    /**
     * @return Expr|null
     */
    public function getWhereCondition()
    {
        return $this->whereCondition;
    }

    /**
     * {@inheritdoc}
     */
    public function executeOn(IConnection $connection)
    {
        $connection->resequenceOrderIndexColumn($this);
    }
}