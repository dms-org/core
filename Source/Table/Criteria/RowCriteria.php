<?php

namespace Iddigital\Cms\Core\Table\Criteria;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\Criteria\OrderingDirection;
use Iddigital\Cms\Core\Table\IColumn;
use Iddigital\Cms\Core\Table\IColumnComponent;
use Iddigital\Cms\Core\Table\IRowCriteria;
use Iddigital\Cms\Core\Table\ITableStructure;

/**
 * The row search criteria interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class RowCriteria implements IRowCriteria
{
    /**
     * @var ITableStructure
     */
    protected $structure;

    /**
     * @var IColumn[]
     */
    protected $columnsToLoad = [];

    /**
     * @var ColumnCondition[]
     */
    protected $conditions = [];

    /**
     * @var ColumnOrdering[]
     */
    protected $orderings = [];

    /**
     * @var ColumnGrouping[]
     */
    protected $groupings = [];

    /**
     * @var int
     */
    protected $rowsToSkip = 0;

    /**
     * @var int|null
     */
    protected $amountOfRows = null;

    /**
     * RowCriteria constructor.
     *
     * @param ITableStructure $structure
     */
    public function __construct(ITableStructure $structure)
    {
        $this->structure = $structure;
    }

    /**
     * @param IRowCriteria $criteria
     *
     * @return RowCriteria
     */
    public static function fromExisting(IRowCriteria $criteria)
    {
        $self = new self($criteria->getStructure());

        $self->columnsToLoad = $criteria->getColumnsToLoad();
        $self->conditions    = $criteria->getConditions();
        $self->orderings     = $criteria->getOrderings();
        $self->groupings     = $criteria->getGroupings();
        $self->rowsToSkip    = $criteria->getRowsToSkip();
        $self->amountOfRows  = $criteria->getAmountOfRows();

        return $self;
    }

    /**
     * {@inheritDoc}
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * {@inheritDoc}
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnsToLoad()
    {
        return $this->columnsToLoad;
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnNamesToLoad()
    {
        return array_keys($this->columnsToLoad);
    }

    /**
     * {@inheritDoc}
     */
    public function getWhetherLoadsAllColumns()
    {
        return count(array_diff_key($this->structure->getColumns(), $this->columnsToLoad)) === 0;
    }

    /**
     * Loads the supplied columns or load all the columns if null.
     *
     * @param array|null $columnNames
     *
     * @return static
     */
    public function loadAll(array $columnNames = null)
    {
        if (is_array($columnNames)) {
            foreach ($columnNames as $columnName) {
                $this->columnsToLoad[$columnName] = $this->structure->getColumn($columnName);
            }
        } else {
            $this->columnsToLoad = $this->structure->getColumns();
        }

        return $this;
    }

    /**
     * Loads the supplied column.
     *
     * @param string $columnName
     *
     * @return static
     */
    public function load($columnName)
    {
        $this->columnsToLoad[$columnName] = $this->structure->getColumn($columnName);

        return $this;
    }

    /**
     * Adds a condition
     *
     * @param string $componentId
     * @param string $operator
     * @param mixed  $value
     *
     * @return static
     */
    public function where($componentId, $operator, $value)
    {
        /** @var IColumnComponent $component */
        list($column, $component) = $this->structure->getColumnAndComponent($componentId);

        $operator           = $component->getType()->getOperator($operator);
        $this->conditions[] = new ColumnCondition(
                $column,
                $component,
                $operator,
                $operator->getField()->process($value)
        );

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderings()
    {
        return $this->orderings;
    }

    /**
     * Adds an ordering
     *
     * @param string $componentId
     * @param string $direction
     *
     * @return static
     */
    public function orderBy($componentId, $direction)
    {
        list($column, $component) = $this->structure->getColumnAndComponent($componentId);

        $this->orderings[] = new ColumnOrdering($column, $component, $direction);

        return $this;
    }

    /**
     * Adds an ASC ordering
     *
     * @param string $componentId
     *
     * @return static
     */
    public function orderByAsc($componentId)
    {
        return $this->orderBy($componentId, OrderingDirection::ASC);
    }

    /**
     * Adds an DESC ordering
     *
     * @param string $componentId
     *
     * @return static
     */
    public function orderByDesc($componentId)
    {
        return $this->orderBy($componentId, OrderingDirection::DESC);
    }

    /**
     * @inheritDoc
     */
    public function getGroupings()
    {
        return $this->groupings;
    }

    /**
     * Adds a column component to section the rows into groups.
     *
     * @param string $componentId
     *
     * @return static
     */
    public function groupBy($componentId)
    {
        /** @var IColumn $column */
        list($column, $component) = $this->structure->getColumnAndComponent($componentId);
        $this->groupings[] = new ColumnGrouping($column, $component);

        $this->load($column->getName());

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRowsToSkip()
    {
        return $this->rowsToSkip;
    }

    /**
     * @param int $rowNumber
     *
     * @return static
     */
    public function skipRows($rowNumber)
    {
        $this->rowsToSkip = (int)$rowNumber;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getAmountOfRows()
    {
        return $this->amountOfRows;
    }

    /**
     * @param int $amountOfRows
     *
     * @return static
     */
    public function maxRows($amountOfRows)
    {
        $this->amountOfRows = (int)$amountOfRows;

        return $this;
    }
}
