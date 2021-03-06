<?php declare(strict_types = 1);

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
    public function __construct(IChartAxis $axis, string $direction)
    {
        OrderingDirection::validate($direction);
        parent::__construct($axis);

        $this->direction = $direction;
    }

    /**
     * @return string
     */
    public function getDirection() : string
    {
        return $this->direction;
    }

    /**
     * @return bool
     */
    public function isAsc() : bool
    {
        return $this->direction === OrderingDirection::ASC;
    }
}
