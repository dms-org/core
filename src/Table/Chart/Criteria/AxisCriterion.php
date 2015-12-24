<?php

namespace Dms\Core\Table\Chart\Criteria;

use Dms\Core\Exception;
use Dms\Core\Table\Chart\IChartAxis;
use Dms\Core\Table\IColumnComponent;

/**
 * The axis criterion base class
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
abstract class AxisCriterion
{
    /**
     * @var IChartAxis
     */
    protected $axis;

    /**
     * @var IColumnComponent
     */
    protected $component;

    /**
     * AxisCriterion constructor.
     *
     * @param IChartAxis $axis
     */
    public function __construct(IChartAxis $axis)
    {
        $this->axis = $axis;
    }

    /**
     * @return IChartAxis
     */
    final public function getAxis()
    {
        return $this->axis;
    }
}
