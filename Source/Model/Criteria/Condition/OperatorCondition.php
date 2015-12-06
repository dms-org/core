<?php

namespace Iddigital\Cms\Core\Model\Criteria\Condition;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\IType;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The operator condition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class OperatorCondition extends Condition
{
    /**
     * @var string
     */
    protected $conditionOperator;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * OperatorCondition constructor.
     *
     * @param IType    $expressionType
     * @param string   $conditionOperator
     * @param mixed    $value
     *
     * @throws InvalidArgumentException
     * @throws TypeMismatchException
     */
    public function __construct(IType $expressionType, $conditionOperator, $value)
    {
        $operators = $expressionType->getConditionOperatorTypes();

        if (!isset($operators[$conditionOperator])) {
            throw InvalidArgumentException::format(
                    'Invalid condition operator for %s of type %s: expecting one of (%s), %s given',
                    $this->debugExpressionString(), $expressionType->asTypeString(), Debug::formatValues(array_keys($operators)),
                    $conditionOperator
            );
        }

        $valueType = $operators[$conditionOperator]->getValueType();
        if (!$valueType->isOfType($value)) {
            throw TypeMismatchException::format(
                    'Invalid condition value for %s with operator \'%s\': expecting value to match member type %s, %s given',
                    $this->debugExpressionString(), $conditionOperator, $valueType->asTypeString(), Type::from($value)->asTypeString()
            );
        }

        ConditionOperator::validate($conditionOperator);

        $nullSafeOperators = [ConditionOperator::EQUALS, ConditionOperator::NOT_EQUALS];
        if ($value === null && !in_array($conditionOperator, $nullSafeOperators, true)) {
            throw InvalidArgumentException::format(
                    'Invalid condition operator for %s of type %s: only the (%s) operators support a NULL operand, %s given',
                    $this->debugExpressionString(), $expressionType->asTypeString(), Debug::formatValues($nullSafeOperators),
                    $conditionOperator
            );
        }

        $this->conditionOperator        = $conditionOperator;
        $this->value                    = $value;

        parent::__construct();
    }

    /**
     * @return string
     */
    abstract protected function debugExpressionString();

    /**
     * @return string
     */
    final public function getOperator()
    {
        return $this->conditionOperator;
    }

    /**
     * @return mixed
     */
    final public function getValue()
    {
        return $this->value;
    }

    /**
     * @return callable
     */
    abstract protected function makeArrayGetterCallback();

    protected function makeArrayFilterCallable()
    {
        $getter = $this->makeArrayGetterCallback();
        $value  = $this->value;

        $comparer = ConditionOperator::makeOperatorCallable(
                function ($memberValue) {
                    return $memberValue;
                },
                $this->conditionOperator,
                function () use ($value) {
                    return $value;
                }
        );

        return function (array $objects) use ($getter, $comparer) {
            $values   = $getter($objects);
            $filtered = [];

            foreach ($values as $key => $memberValue) {
                if ($comparer($memberValue)) {
                    $filtered[$key] = $objects[$key];
                }
            }

            return $filtered;
        };
    }
}