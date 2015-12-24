<?php

namespace Dms\Core\Table\Criteria;

use Dms\Core\Exception;
use Dms\Core\Model\Criteria\OrderingDirection;
use Dms\Core\Table\IColumn;
use Dms\Core\Table\IColumnComponent;

/**
 * The column ordering class
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class ColumnOrdering extends ColumnCriterion
{
    /**
     * @var string
     */
    protected $direction;

    /**
     * ColumnCondition constructor.
     *
     * @param IColumn          $column
     * @param IColumnComponent $component
     * @param string           $direction
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(IColumn $column, IColumnComponent $component, $direction)
    {
        OrderingDirection::validate($direction);
        parent::__construct($column, $component);

        $this->direction = $direction;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @return bool
     */
    public function isAsc()
    {
        return $this->direction === OrderingDirection::ASC;
    }
}
