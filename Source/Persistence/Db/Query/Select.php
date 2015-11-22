<?php

namespace Iddigital\Cms\Core\Persistence\Db\Query;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\ColumnExpr;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The db select query class.
 *
 * This should return the result set matched by the query criteria.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Select extends Query
{
    /**
     * @var string[]
     */
    private $outerSelectAliases = [];

    /**
     * @var Expr[]|null
     */
    private $aliasColumnMap = null;

    /**
     * @var Expr[]
     */
    private $groupBy = [];

    /**
     * @var Expr[]
     */
    private $having = [];

    /**
     * @param Table $table
     *
     * @return Select
     */
    public static function allFrom(Table $table)
    {
        $select = new Select($table);

        foreach ($table->getColumnNames() as $columnNames) {
            $select->addRawColumn($columnNames);
        }

        return $select;
    }

    /**
     * {@inheritdoc}
     */
    public function executeOn(IConnection $connection)
    {
        return $connection->load($this);
    }

    /**
     * Builds a select for use as a subselect within this select instance.
     *
     * @param Table $fromTable
     *
     * @return Select
     */
    public function buildSubSelect(Table $fromTable)
    {
        $subSelect = Select::from($fromTable);
        $subSelect->setAlias($this->generateUniqueAliasFor($fromTable->getName()));

        $subSelect->outerSelectAliases = $this->getTakenAliases();

        return $subSelect;
    }

    /**
     * Gets the structure of the result set of this select.
     *
     * @return Table
     */
    public function getResultSetTableStructure()
    {
        $columns = [];

        foreach ($this->aliasColumnMap as $alias => $expr) {
            $columns[] = new Column($alias,  $expr->getResultingType());
        }

        return new Table('__result_set__', $columns);
    }

    /**
     * @return Expr[]
     */
    public function getAliasColumnMap()
    {
        return $this->aliasColumnMap;
    }

    /**
     * @return string[]
     */
    protected function getTakenAliases()
    {
        return array_merge(parent::getTakenAliases(), $this->outerSelectAliases);
    }

    /**
     * @param string $alias
     * @param Expr   $column
     *
     * @return static
     */
    public function addColumn($alias, Expr $column)
    {
        $this->aliasColumnMap[$alias] = $column;

        return $this;
    }

    /**
     * Adds a column from the FROM table.
     *
     * @param string $column
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function addRawColumn($column)
    {
        $table = $this->getTable();

        if (!$table->hasColumn($column)) {
            throw InvalidArgumentException::format(
                    'Invalid column for select: must be one of (%s), \'%s\' given',
                    Debug::formatValues($table->getColumnNames()), $column
            );
        }

        $this->aliasColumnMap[$column] = Expr::column($this->getTableAlias(), $table->findColumn($column));

        return $this;
    }

    /**
     * Adds the columns from the FROM table.
     *
     * @param Expr[] $aliasColumnMap
     *
     * @return static
     */
    public function setColumns(array $aliasColumnMap)
    {
        $this->aliasColumnMap = $aliasColumnMap;

        return $this;
    }

    /**
     * @return Expr[]
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * @param Expr $groupBy
     *
     * @return static
     */
    public function addGroupBy(Expr $groupBy)
    {
        $this->groupBy[] = $groupBy;

        return $this;
    }

    /**
     * @return Expr[]
     */
    public function getHaving()
    {
        return $this->having;
    }

    /**
     * @param Expr $having
     *
     * @return static
     */
    public function addHaving(Expr $having)
    {
        $this->having[] = $having;

        return $this;
    }

    /**
     * @return static
     */
    public function removeOuterSelectAliases()
    {
        $this->outerSelectAliases = [];

        return $this;
    }
}