<?php declare(strict_types = 1);

namespace Dms\Core\Table\Chart\Criteria;

use Dms\Core\Exception;
use Dms\Core\Model\Criteria\OrderingDirection;
use Dms\Core\Table\Chart\IChartCriteria;
use Dms\Core\Table\Chart\IChartStructure;

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
     * @param IChartCriteria $criteria
     *
     * @return ChartCriteria
     */
    public static function fromExisting(IChartCriteria $criteria) : ChartCriteria
    {
        $self = new self($criteria->getStructure());

        $self->conditions = $criteria->getConditions();
        $self->orderings  = $criteria->getOrderings();

        return $self;
    }

    /**
     * {@inheritDoc}
     */
    public function getStructure() : \Dms\Core\Table\Chart\IChartStructure
    {
        return $this->structure;
    }

    /**
     * {@inheritDoc}
     */
    public function getConditions() : array
    {
        return $this->conditions;
    }

    /**
     * Adds a condition
     *
     * @param string $axisName
     * @param string $operator
     * @param mixed  $value
     * @param bool   $isProcessed
     *
     * @return static
     */
    public function where(string $axisName, string $operator, $value, bool $isProcessed = false)
    {
        $axis = $this->structure->getAxis($axisName);

        $operator           = $axis->getType()->getOperator($operator);
        $this->conditions[] = new AxisCondition(
            $axis,
            $operator,
            $isProcessed ? $value : $operator->getField()->process($value)
        );

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
     * Adds an ordering
     *
     * @param string $axisName
     * @param string $direction
     *
     * @return static
     */
    public function orderBy(string $axisName, string $direction)
    {
        $axis = $this->structure->getAxis($axisName);

        $this->orderings[] = new AxisOrdering($axis, $direction);

        return $this;
    }

    /**
     * Adds an ASC ordering
     *
     * @param string $axisName
     *
     * @return static
     */
    public function orderByAsc(string $axisName)
    {
        return $this->orderBy($axisName, OrderingDirection::ASC);
    }

    /**
     * Adds an DESC ordering
     *
     * @param string $axisName
     *
     * @return static
     */
    public function orderByDesc(string $axisName)
    {
        return $this->orderBy($axisName, OrderingDirection::DESC);
    }

    /**
     * @inheritDoc
     */
    public function asNewCriteria() : ChartCriteria
    {
        return clone $this;
    }
}
