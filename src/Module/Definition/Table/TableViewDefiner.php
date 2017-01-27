<?php declare(strict_types = 1);

namespace Dms\Core\Module\Definition\Table;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\Table\TableView;
use Dms\Core\Table\IRowCriteria;
use Dms\Core\Table\ITableDataSource;

/**
 * The table view definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableViewDefiner
{
    /**
     * @var ITableDataSource
     */
    protected $dataSource;

    /**
     * @var IRowCriteria
     */
    protected $rowCriteria;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $label;

    /**
     * @var bool
     */
    private $default = false;

    /**
     * TableViewDefiner constructor.
     *
     * @param ITableDataSource $dataSource
     * @param string           $name
     * @param string           $label
     */
    public function __construct(ITableDataSource $dataSource, string $name, string $label)
    {
        $this->dataSource  = $dataSource;
        $this->rowCriteria = $dataSource->criteria();
        $this->dataSource  = $dataSource;
        $this->name        = $name;
        $this->label       = $label;
    }

    /**
     * Defines this view to be the default view.
     *
     * @return static
     */
    public function asDefault()
    {
        $this->default = true;

        return $this;
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
        $this->rowCriteria->loadAll($columnNames);

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
        $this->rowCriteria->load($columnName);

        return $this;
    }

    /**
     * Clears the loaded columns.
     *
     * @return static
     */
    public function clearLoadedColumns()
    {
        $this->rowCriteria->clearLoadedColumns();

        return $this;
    }

    /**
     * Sets the condition mode of the criteria.
     *
     * @param string $conditionMode
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function setConditionMode(string $conditionMode)
    {
        $this->rowCriteria->setConditionMode($conditionMode);

        return $this;
    }

    /**
     * Sets the condition mode of the criteria to AND.
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function setConditionModeToAnd()
    {
        $this->rowCriteria->setConditionModeToAnd();

        return $this;
    }

    /**
     * Sets the condition mode of the criteria to OR.
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function setConditionModeToOr()
    {
        $this->rowCriteria->setConditionModeToOr();

        return $this;
    }

    /**
     * Clears the conditions.
     *
     * @return static
     */
    public function clearConditions()
    {
        $this->rowCriteria->clearConditions();

        return $this;
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
        $this->rowCriteria->where($componentId, $operator, $value, $processed);

        return $this;
    }


    /**
     * Clears the orderings.
     *
     * @return static
     */
    public function clearOrderings()
    {
        $this->rowCriteria->clearOrderings();

        return $this;
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
        $this->rowCriteria->orderBy($componentId, $direction);

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
        $this->rowCriteria->orderByAsc($componentId);

        return $this;
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
        $this->rowCriteria->orderByDesc($componentId);

        return $this;
    }

    /**
     * Clears the groupings
     *
     * @return static
     */
    public function clearGroupings()
    {
        $this->rowCriteria->clearGroupings();

        return $this;
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
        $this->rowCriteria->groupBy($componentId);

        return $this;
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
        $this->rowCriteria->skipRows($rowNumber);

        return $this;
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
        $this->rowCriteria->maxRows($amountOfRows);

        return $this;
    }

    /**
     * @return TableView
     */
    public function finalize(): TableView
    {
        return new TableView(
            $this->name,
            $this->label,
            $this->default,
            $this->rowCriteria->asNewCriteria()
        );
    }
}