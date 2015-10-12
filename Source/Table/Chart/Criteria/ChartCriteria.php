<?php

namespace Iddigital\Cms\Core\Table\Chart\Criteria;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Table\Chart\IChartCriteria;
use Iddigital\Cms\Core\Table\Chart\IChartStructure;

/**
 * The chart data criteria interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class ChartCriteria implements IChartCriteria
{
    /**
     * @var IChartStructure
     */
    protected $structure;

    /**
     * @var AxisCondition[]
     */
    protected $conditions = [];

    /**
     * @var AxisCondition[]
     */
    protected $orderings = [];

    /**
     * ChartCriteria constructor.
     *
     * @param IChartStructure $structure
     */
    public function __construct(IChartStructure $structure)
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
     * @param string $axisName
     * @param string $operator
     * @param mixed  $value
     *
     * @return static
     */
    public function where($axisName, $operator, $value)
    {
        $axis = $this->structure->getAxis($axisName);

        $operator           = $axis->getType()->getOperator($operator);
        $this->conditions[] = new AxisCondition(
                $axis,
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
     * @param string $axisName
     * @param string $direction
     *
     * @return static
     */
    public function orderBy($axisName, $direction)
    {
        $axis = $this->structure->getAxis($axisName);

        $this->orderings[] = new AxisOrdering($axis, $direction);

        return $this;
    }
}
