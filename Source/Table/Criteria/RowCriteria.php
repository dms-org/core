<?php

namespace Iddigital\Cms\Core\Table\Criteria;

use Iddigital\Cms\Core\Exception;
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
        list($column, $component) = $this->structure->getColumnAndComponent($componentId);
        $this->groupings[] = new ColumnGrouping($column, $component);

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
