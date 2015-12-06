<?php

namespace Iddigital\Cms\Core\Model\Criteria;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Model\Criteria\Condition\AndCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\Condition;
use Iddigital\Cms\Core\Model\Criteria\Condition\ConditionOperator;
use Iddigital\Cms\Core\Model\Criteria\Condition\InstanceOfCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\MemberCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\NotCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\OrCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\SelfCondition;
use Iddigital\Cms\Core\Model\ISpecification;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * The typed object specification definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SpecificationDefinition extends ObjectCriteriaBase
{
    const THIS_EXPRESSION = 'this';

    /**
     * @var bool
     */
    private $isOrMode = false;

    final protected function append(Condition $condition)
    {
        if ($this->condition === null) {
            $this->condition = $condition;

            return;
        } elseif ($this->isOrMode) {
            $this->condition = new OrCondition([$this->condition, $condition]);
        } else {
            $this->condition = new AndCondition([$this->condition, $condition]);
        }
    }

    /**
     * Defines a where condition on the criteria.
     *
     * Examples:
     * <code>
     * ->where('some.nested.property', '=', 100)
     * //
     * ->where('some.collection.count()', '>', 5)
     * //
     * ->where('some.friends.average(income)', '<=', 50000)
     * //
     * ->where('load(relatedId).name', '=', 'Joe')
     * //
     * ->where('this', '=', $someObject)
     * </code>
     *
     * @param string $memberExpression
     * @param string $operator
     * @param mixed  $value
     *
     * @return static
     * @throws InvalidArgumentException
     * @throws InvalidMemberExpressionException
     */
    final public function where($memberExpression, $operator, $value)
    {
        $this->append(new MemberCondition(
                $this->memberExpressionParser->parse($this->class, $memberExpression),
                $operator,
                $value
        ));

        return $this;
    }

    /**
     * Defines a condition that is satisfied when ANY of the
     * conditions within the callback are satisfied.
     *
     * Example:
     * <code>
     * ->whereAny(function (SpecificationDefinition $match) {
     *      $match->where('prop', '>', 50);
     *      $match->where('prop', '<', 10);
     * })
     * </code>
     *
     * @param callable $conditionCallback
     *
     * @return static
     */
    final public function whereAny(callable $conditionCallback)
    {
        $definition           = new SpecificationDefinition($this->class);
        $definition->isOrMode = true;
        $conditionCallback($definition);
        $this->append($definition->getCondition());

        return $this;
    }

    /**
     * Defines a condition that is satisfied when ALL of the
     * conditions within the callback are satisfied.
     *
     * Example:
     * <code>
     * ->whereAll(function (SpecificationDefinition $match) {
     *      $match->where('prop', '>', 50);
     *      $match->where('flag', '=', true);
     * })
     * </code>
     *
     * @param callable $conditionCallback
     *
     * @return static
     */
    final public function whereAll(callable $conditionCallback)
    {
        $definition = new SpecificationDefinition($this->class);
        $conditionCallback($definition);
        $this->append($definition->getCondition());

        return $this;
    }

    /**
     * Defines a condition that is satisfied when the condition
     * within the callback are NOT satisfied.
     *
     * Example:
     * <code>
     * ->whereNot(function (SpecificationDefinition $match) {
     *      $match->where('prop', '>', 50);
     * })
     * </code>
     *
     * @param callable $conditionCallback
     *
     * @return static
     */
    final public function whereNot(callable $conditionCallback)
    {
        $definition = new SpecificationDefinition($this->class);
        $conditionCallback($definition);
        $this->append(new NotCondition($definition->getCondition()));

        return $this;
    }

    /**
     * Defines a condition that is satisfied when the condition
     * property contains the supplied string.
     *
     * @param string $memberExpression
     * @param mixed  $value
     *
     * @return static
     */
    final public function whereStringContains($memberExpression, $value)
    {
        return $this->where($memberExpression, ConditionOperator::STRING_CONTAINS, $value);
    }

    /**
     * Defines a condition that is satisfied when the condition
     * property contains the supplied string insensitive to case.
     *
     * @param string $memberExpression
     * @param mixed  $value
     *
     * @return static
     */
    final public function whereStringContainsCaseInsensitive($memberExpression, $value)
    {
        return $this->where($memberExpression, ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE, $value);
    }

    /**
     * Defines a condition that is satisfied when the value
     * is found within the array/collection of the property.
     *
     * @param string $memberExpression
     * @param array  $values
     *
     * @return static
     */
    final public function whereIn($memberExpression, array $values)
    {
        return $this->where($memberExpression, ConditionOperator::IN, $values);
    }

    /**
     * Defines a condition that is satisfied when the value
     * is NOT found within the array/collection of the property.
     *
     * @param string $memberExpression
     * @param array  $values
     *
     * @return static
     */
    final public function whereNotIn($memberExpression, array $values)
    {
        return $this->where($memberExpression, ConditionOperator::NOT_IN, $values);
    }

    /**
     * Defines a condition that is satisfied when the class
     * is an instance of the supplied class.
     *
     * @param string $class
     *
     * @return static
     * @throws InvalidArgumentException
     */
    final public function whereInstanceOf($class)
    {
        if (!is_a($class, $this->class->getClassName(), true)) {
            throw InvalidArgumentException::format(
                    'Invalid class supplied to %s: must be an a subclass of %s, %s given',
                    __METHOD__, $this->class->getClassName(), $class
            );
        }

        $this->append(new InstanceOfCondition($class));

        return $this;
    }

    /**
     * Defines a condition that is satisfied when the value
     * supplied specification is satisfied.
     *
     * @param ISpecification $specification
     *
     * @return static
     * @throws TypeMismatchException
     */
    final public function whereSatisfies(ISpecification $specification)
    {
        $specification->verifyOfClass($this->class->getClassName());

        $this->append($specification->getCondition());

        return $this;
    }
}