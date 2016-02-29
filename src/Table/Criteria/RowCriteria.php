<?php declare(strict_types = 1);

namespace Dms\Core\Table\Criteria;

use Dms\Core\Exception;
use Dms\Core\Model\Criteria\OrderingDirection;
use Dms\Core\Table\IColumn;
use Dms\Core\Table\IColumnComponent;
use Dms\Core\Table\IRowCriteria;
use Dms\Core\Table\ITableStructure;
use Dms\Core\Util\Debug;

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
     * @var string
     */
    protected $conditionMode = self::CONDITION_MODE_AND;

    /**
     * @var ColumnConditionGroup[]
     */
    protected $conditionGroups = [];

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
    public static function fromExisting(IRowCriteria $criteria) : RowCriteria
    {
        $self = new self($criteria->getStructure());

        $self->columnsToLoad   = $criteria->getColumnsToLoad();
        $self->conditionMode   = $criteria->getConditionMode();
        $self->conditionGroups = $criteria->getConditionGroups();
        $self->orderings       = $criteria->getOrderings();
        $self->groupings       = $criteria->getGroupings();
        $self->rowsToSkip      = $criteria->getRowsToSkip();
        $self->amountOfRows    = $criteria->getAmountOfRows();

        return $self;
    }

    /**
     * {@inheritDoc}
     */
    public function getStructure() : ITableStructure
    {
        return $this->structure;
    }

    /**
     * {@inheritDoc}
     */
    public function getConditionMode() : string
    {
        return $this->conditionMode;
    }

    /**
     * {@inheritDoc}
     */
    public function getConditionGroups() : array
    {
        return $this->conditionGroups;
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnsToLoad() : array
    {
        return $this->columnsToLoad;
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnNamesToLoad() : array
    {
        return array_keys($this->columnsToLoad);
    }

    /**
     * {@inheritDoc}
     */
    public function getWhetherLoadsAllColumns() : bool
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
    public function load(string $columnName)
    {
        $this->columnsToLoad[$columnName] = $this->structure->getColumn($columnName);

        return $this;
    }

    /**
     * Sets the condition mode of the criteria.
     *
     * @param string $conditionMode
     *
     * @return static
     * @throws Exception\InvalidArgumentException
     */
    public function setConditionMode(string $conditionMode)
    {
        if (!in_array($conditionMode, [self::CONDITION_MODE_AND, self::CONDITION_MODE_OR], true)) {
            throw Exception\InvalidArgumentException::format(
                'Invalid condition mode supplied to %s: expecting one of (%s), \'%s\' given',
                __METHOD__, Debug::formatValues([self::CONDITION_MODE_AND, self::CONDITION_MODE_OR]), $conditionMode
            );
        }

        $this->conditionMode = $conditionMode;

        return $this;
    }

    /**
     * Sets the condition mode of the criteria to AND.
     *
     * @return static
     * @throws Exception\InvalidArgumentException
     */
    public function setConditionModeToAnd()
    {
        return $this->setConditionMode(self::CONDITION_MODE_AND);
    }

    /**
     * Sets the condition mode of the criteria to OR.
     *
     * @return static
     * @throws Exception\InvalidArgumentException
     */
    public function setConditionModeToOr()
    {
        return $this->setConditionMode(self::CONDITION_MODE_OR);
    }

    /**
     * Adds a condition
     *
     * @param string $componentId
     * @param string $operator
     * @param mixed  $value
     * @param bool   $processed
     *
     * @return static
     */
    public function where(string $componentId, string $operator, $value, bool $processed = false)
    {
        /** @var IColumnComponent $component */
        list($column, $component) = $this->structure->getColumnAndComponent($componentId);

        $operator = $component->getType()->getOperator($operator);
        /** @var ColumnConditionGroup $lastGroup */
        $lastGroup = end($this->conditionGroups);

        $condition = new ColumnCondition(
            $column,
            $component,
            $operator,
            $processed ? $value : $operator->getField()->process($value)
        );

        if (!$lastGroup) {
            $this->conditionGroups[] = new ColumnConditionGroup($this->conditionMode, [$condition]);
        } elseif ($lastGroup->getConditionMode() !== $this->conditionMode) {
            $this->conditionGroups[] = new ColumnConditionGroup($this->conditionMode, [$condition]);
        } else {
            array_pop($this->conditionGroups);
            $this->conditionGroups[] = new ColumnConditionGroup(
                $this->conditionMode,
                array_merge($lastGroup->getConditions(), [$condition])
            );
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderings() : array
    {
        return $this->orderings;
    }

    /**
     * Adds an ordering on the supplied column component.
     *
     * @param string $componentId
     * @param string $direction
     *
     * @return static
     */
    public function orderBy(string $componentId, string $direction)
    {
        list($column, $component) = $this->structure->getColumnAndComponent($componentId);

        $this->orderings[] = new ColumnOrdering($column, $component, $direction);

        return $this;
    }

    /**
     * Adds an ascending ordering on the supplied column component.
     *
     * @param string $componentId
     *
     * @return static
     */
    public function orderByAsc(string $componentId)
    {
        return $this->orderBy($componentId, OrderingDirection::ASC);
    }

    /**
     * Adds an descending ordering on the supplied column component.
     *
     * @param string $componentId
     *
     * @return static
     */
    public function orderByDesc(string $componentId)
    {
        return $this->orderBy($componentId, OrderingDirection::DESC);
    }

    /**
     * @inheritDoc
     */
    public function getGroupings() : array
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
    public function groupBy(string $componentId)
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
    public function getRowsToSkip() : int
    {
        return $this->rowsToSkip;
    }

    /**
     * Skips the supplied number of rows from the start.
     *
     * @param int $rowNumber
     *
     * @return static
     */
    public function skipRows(int $rowNumber)
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
     * Limits the amount of rows to the supplied number.
     *
     * @param int $amountOfRows
     *
     * @return static
     */
    public function maxRows(int $amountOfRows)
    {
        $this->amountOfRows = (int)$amountOfRows;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function asNewCriteria() : RowCriteria
    {
        return clone $this;
    }
}
