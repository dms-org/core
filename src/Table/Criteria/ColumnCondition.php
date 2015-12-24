<?php

namespace Dms\Core\Table\Criteria;

use Dms\Core\Exception;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Table\IColumn;
use Dms\Core\Table\IColumnComponent;
use Dms\Core\Table\IColumnComponentOperator;

/**
 * The column condition class
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class ColumnCondition extends ColumnCriterion
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
     * ColumnCondition constructor.
     *
     * @param IColumn                  $column
     * @param IColumnComponent         $component
     * @param IColumnComponentOperator $operator
     * @param mixed                    $value
     *
     * @throws Exception\TypeMismatchException
     */
    public function __construct(IColumn $column, IColumnComponent $component, IColumnComponentOperator $operator, $value)
    {
        $type = $operator->getField()->getProcessedType();
        if (!$type->isOfType($value)) {
            throw Exception\TypeMismatchException::argument(__METHOD__, 'value', $type->asTypeString(), $value);
        }

        parent::__construct($column, $component);

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

    /**
     * @return callable
     */
    public function makeRowFilterCallable()
    {
        $value = $this->value;

        return ConditionOperator::makeOperatorCallable(
                $this->makeComponentGetterCallable(),
                $this->operator->getOperator(),
                function () use ($value) {
                    return $value;
                }
        );
    }
}
