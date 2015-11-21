<?php

namespace Iddigital\Cms\Core\Model\Criteria\Condition;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Model\Criteria\NestedMember;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The member condition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MemberCondition extends Condition
{
    /**
     * @var NestedMember
     */
    private $member;

    /**
     * @var string
     */
    private $conditionOperator;

    /**
     * @var mixed
     */
    private $value;

    /**
     * MemberCondition constructor.
     *
     * @param NestedMember $member
     * @param string       $conditionOperator
     * @param mixed        $value
     *
     * @throws InvalidArgumentException
     * @throws TypeMismatchException
     */
    final public function __construct(NestedMember $member, $conditionOperator, $value)
    {
        $expressionType = $member->getResultingType();

        $operators = $expressionType->getConditionOperatorTypes();

        if (!isset($operators[$conditionOperator])) {
            throw InvalidArgumentException::format(
                    'Invalid condition operator for member \'%s\' of type %s: expecting one of (%s), %s given',
                    $member->asString(), $expressionType->asTypeString(), Debug::formatValues(array_keys($operators)), $conditionOperator
            );
        }

        $valueType = $operators[$conditionOperator]->getValueType();
        if (!$valueType->isOfType($value)) {
            throw TypeMismatchException::format(
                    'Invalid condition value for member \'%s\' with operator \'%s\': expecting value to match member type %s, %s given',
                    $member->asString(), $conditionOperator, $valueType->asTypeString(), Type::from($value)->asTypeString()
            );
        }

        ConditionOperator::validate($conditionOperator);

        $nullSafeOperators = [ConditionOperator::EQUALS, ConditionOperator::NOT_EQUALS];
        if ($value === null && !in_array($conditionOperator, $nullSafeOperators, true)) {
            throw InvalidArgumentException::format(
                    'Invalid condition operator for member \'%s\' of type %s: only the (%s) operators support a NULL operand, %s given',
                    $member->asString(), $expressionType->asTypeString(), Debug::formatValues($nullSafeOperators), $conditionOperator
            );
        }

        $this->member            = $member;
        $this->conditionOperator = $conditionOperator;
        $this->value             = $value;

        parent::__construct();
    }

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
     * @return NestedMember
     */
    final public function getNestedMember()
    {
        return $this->member;
    }

    protected function makeArrayFilterCallable()
    {
        $getter = $this->member->makeArrayGetterCallable();
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