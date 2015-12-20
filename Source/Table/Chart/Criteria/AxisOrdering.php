<?php

namespace Dms\Core\Table\Chart\Criteria;

use Dms\Core\Exception;
use Dms\Core\Model\Criteria\OrderingDirection;
use Dms\Core\Table\Chart\IChartAxis;

/**
 * The column ordering class
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class AxisOrdering extends AxisCriterion
{
    /**
     * @var string
     */
    protected $direction;

    /**
     * ColumnCondition constructor.
     *
     * @param IChartAxis $axis
     * @param string     $direction
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(IChartAxis $axis, $direction)
    {
        OrderingDirection::validate($direction);
        parent::__construct($axis);

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
