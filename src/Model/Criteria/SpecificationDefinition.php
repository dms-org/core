<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria;

use Dms\Core\Exception;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Model\Criteria\Condition\AndCondition;
use Dms\Core\Model\Criteria\Condition\Condition;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Model\Criteria\Condition\InstanceOfCondition;
use Dms\Core\Model\Criteria\Condition\MemberCondition;
use Dms\Core\Model\Criteria\Condition\NotCondition;
use Dms\Core\Model\Criteria\Condition\OrCondition;
use Dms\Core\Model\EntityIdCollection;
use Dms\Core\Model\ISpecification;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Object\TypedObject;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\CollectionType;

/**
 * The typed object specification definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SpecificationDefinition extends ObjectCriteriaBase
{
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
    final public function where(string $memberExpression, string $operator, $value)
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
        $definition           = new SpecificationDefinition($this->class, $this->memberExpressionParser);
        $definition->isOrMode = true;
        $conditionCallback($definition);

        if ($definition->hasCondition()) {
            $this->append($definition->getCondition());
        }

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
        $definition = new SpecificationDefinition($this->class, $this->memberExpressionParser);
        $conditionCallback($definition);

        if ($definition->hasCondition()) {
            $this->append($definition->getCondition());
        }

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
        $definition = new SpecificationDefinition($this->class, $this->memberExpressionParser);
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
    final public function whereStringContains(string $memberExpression, $value)
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
    final public function whereStringContainsCaseInsensitive(string $memberExpression, $value)
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
    final public function whereIn(string $memberExpression, array $values)
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
    final public function whereNotIn(string $memberExpression, array $values)
    {
        return $this->where($memberExpression, ConditionOperator::NOT_IN, $values);
    }

    /**
     * Defines a condition that is satisfied when the value
     * ALL the objects in the collection satisfy the supplied specification.
     *
     * @param string         $collectionMemberExpression
     * @param ISpecification $specification
     *
     * @return static
     */
    final public function whereHasAll(string $collectionMemberExpression, ISpecification $specification)
    {
        return $this->where($collectionMemberExpression, ConditionOperator::ALL_SATISFIES, $specification);
    }

    /**
     * Defines a condition that is satisfied when the value
     * ANY of the objects in the collection satisfy the supplied specification.
     *
     * @param string         $collectionMemberExpression
     * @param ISpecification $specification
     *
     * @return static
     */
    final public function whereHasAny(string $collectionMemberExpression, ISpecification $specification)
    {
        return $this->where($collectionMemberExpression, ConditionOperator::ANY_SATISFIES, $specification);
    }

    /**
     * Defines a condition that is satisfied when the collection
     * contains the supplied item.
     *
     * @param string $collectionMemberExpression
     * @param mixed  $item
     *
     * @return static
     * @throws InvalidArgumentException
     * @throws InvalidMemberExpressionException
     */
    public function whereCollectionContains(string $collectionMemberExpression, $item)
    {
        $expression           = $this->memberExpressionParser->parse($this->class, $collectionMemberExpression);
        $isEntityIdCollection = $expression->getResultingType()->nonNullable()->isSubsetOf(EntityIdCollection::type());

        if ($isEntityIdCollection) {
            $memberParts                = explode('.', $collectionMemberExpression);
            $lastPart                   = array_pop($memberParts);
            $memberParts[]              = 'loadAll(' . $lastPart . ')';
            $collectionMemberExpression = implode('.', $memberParts);
            $expression                 = $this->memberExpressionParser->parse($this->class, $collectionMemberExpression);
        }

        /** @var CollectionType $collectionType */
        $collectionType = $expression->getResultingType()->nonNullable();
        if (!$collectionType->isSubsetOf(Type::collectionOf(TypedObject::type()))) {
            throw InvalidMemberExpressionException::format(
                'Invalid collection member expression supplied to %s: expecting type %s, %s (\'%s\') given',
                __METHOD__, Type::collectionOf(TypedObject::type())->asTypeString(),
                $collectionType->asTypeString(), $collectionMemberExpression
            );
        }

        /** @var string|TypedObject $objectType */
        $objectType = $collectionType->getElementType()->asTypeString();

        if ($isEntityIdCollection) {
            if (!is_int($item)) {
                throw InvalidArgumentException::format(
                    'Invalid collection item supplied to %s: expecting type %s, %s given',
                    __METHOD__, $objectType, Type::from($item)->asTypeString()
                );
            }

            $specification = $objectType::specification(function (SpecificationDefinition $match) use ($item) {
                $match->where(Entity::ID, '=', $item);
            });
        } else {
            if (!($item instanceof $objectType)) {
                throw InvalidArgumentException::format(
                    'Invalid collection item supplied to %s: expecting type %s, %s given',
                    __METHOD__, $objectType, Type::from($item)->asTypeString()
                );
            }

            $specification = $objectType::specification(function (SpecificationDefinition $match) use ($item) {
                $match->where('this', '=', $item);
            });
        }

        return $this->whereHasAny($collectionMemberExpression, $specification);
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
    final public function whereInstanceOf(string $class)
    {
        if ($this->class === $class) {
            return $this;
        }

        if (!is_a($class, $this->class->getClassName(), true)) {
            throw InvalidArgumentException::format(
                'Invalid class supplied to %s: must be an a subclass of %s, %s given',
                __METHOD__, $this->class->getClassName(), $class
            );
        }

        $this->append(new InstanceOfCondition($class));
        
        if (!$this->isOrMode) {
            $this->class = $class::definition();
        }

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

    /**
     * Defines a condition that is satisfied when the member matches
     * the supplied specification.
     *
     * @param string         $memberExpression
     * @param ISpecification $specification
     *
     * @return static
     */
    final public function whereMemberSatisfies(string $memberExpression, ISpecification $specification)
    {
        $this->whereSatisfies(
            $specification
                ->forMemberOf($this->class, $this->getMemberExpressionParser()->parse($this->class, $memberExpression))
        );

        return $this;
    }
}