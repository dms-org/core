<?php

namespace Iddigital\Cms\Core\Table\Criteria;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Table\IColumn;
use Iddigital\Cms\Core\Table\IColumnComponent;
use Iddigital\Cms\Core\Table\ITableRow;

/**
 * The column criterion base class
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
abstract class ColumnCriterion
{
    /**
     * @var IColumn
     */
    protected $column;

    /**
     * @var IColumnComponent
     */
    protected $component;

    /**
     * ColumnCriterion constructor.
     *
     * @param IColumn          $column
     * @param IColumnComponent $component
     */
    public function __construct(IColumn $column, IColumnComponent $component)
    {
        $this->column    = $column;
        $this->component = $component;
    }

    /**
     * @return IColumn
     */
    final public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return IColumnComponent
     */
    final public function getColumnComponent()
    {
        return $this->component;
    }
    
    final public function getComponentId()
    {
        return $this->column->getName() . '.' . $this->component->getName();
    }

    /**
     * @return callable
     */
    final public function makeComponentGetterCallable()
    {
        $column    = $this->column;
        $component = $this->component;

        return function (ITableRow $row) use ($column, $component) {
            return $row->getCellComponentData($column, $component);
        };
    }
}
