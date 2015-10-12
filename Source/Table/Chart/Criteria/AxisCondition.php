<?php

namespace Iddigital\Cms\Core\Table\Chart\Criteria;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Table\Chart\IChartAxis;
use Iddigital\Cms\Core\Table\IColumnComponentOperator;

/**
 * The column condition class
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class AxisCondition extends AxisCriterion
{
    /**
     * @var IColumnComponentOperator
     */
    protected $operator;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * AxisCondition constructor.
     *
     * @param IChartAxis               $axis
     * @param IColumnComponentOperator $operator
     * @param mixed                    $value
     *
     * @throws Exception\TypeMismatchException
     */
    public function __construct(IChartAxis $axis, IColumnComponentOperator $operator, $value)
    {
        $type = $operator->getField()->getProcessedType();
        if (!$type->isOfType($value)) {
            throw Exception\TypeMismatchException::argument(__METHOD__, 'value', $type->asTypeString(), $value);
        }

        parent::__construct($axis);

        $this->operator = $operator;
        $this->value    = $value;
    }

    /**
     * @return IColumnComponentOperator
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
