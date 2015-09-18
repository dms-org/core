<?php

namespace Iddigital\Cms\Core\Model\Criteria;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Model\Criteria\Condition\AndCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\Condition;
use Iddigital\Cms\Core\Model\Criteria\Condition\ConditionOperator;
use Iddigital\Cms\Core\Model\Criteria\Condition\InstanceOfCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\NotCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\OrCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\PropertyCondition;
use Iddigital\Cms\Core\Model\ISpecification;
use Iddigital\Cms\Core\Model\Object\FinalizedPropertyDefinition;
use Iddigital\Cms\Core\Model\Object\TypedObject;
use Iddigital\Cms\Core\Model\Type\ObjectType;

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
     * @param string $propertyName
     * @param string $operator
     * @param mixed  $value
     *
     * @return static
     * @throws InvalidArgumentException
     */
    final public function where($propertyName, $operator, $value)
    {
        $this->append(new PropertyCondition(
                $this->parseNestedProperties($propertyName),
                $operator,
                $value
        ));

        return $this;
    }

    /**
     * @param callable $conditionCallback
     *
     * @return static
     */
    final public function whereAny(callable $conditionCallback)
    {
        $definition = new SpecificationDefinition($this->class);
        $definition->isOrMode = true;
        $conditionCallback($definition);
        $this->append($definition->getCondition());

        return $this;
    }

    /**
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
     * @param callable $conditionCallback
     *
     * @return static
     */
    public function whereNot(callable $conditionCallback)
    {
        $definition = new SpecificationDefinition($this->class);
        $conditionCallback($definition);
        $this->append(new NotCondition($definition->getCondition()));

        return $this;
    }

    /**
     * @param string $propertyName
     * @param mixed  $value
     *
     * @return static
     */
    final public function whereStringContains($propertyName, $value)
    {
        return $this->where($propertyName, ConditionOperator::STRING_CONTAINS, $value);
    }

    /**
     * @param string $propertyName
     * @param mixed  $value
     *
     * @return static
     */
    final public function whereStringContainsCaseInsensitive($propertyName, $value)
    {
        return $this->where($propertyName, ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE, $value);
    }

    /**
     * @param string $propertyName
     * @param array  $values
     *
     * @return static
     */
    final public function whereIn($propertyName, array $values)
    {
        return $this->where($propertyName, ConditionOperator::IN, $values);
    }

    /**
     * @param string $propertyName
     * @param array  $values
     *
     * @return static
     */
    final public function whereNotIn($propertyName, array $values)
    {
        return $this->where($propertyName, ConditionOperator::NOT_IN, $values);
    }

    /**
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
     * @param string $propertyName
     *
     * @return FinalizedPropertyDefinition[]
     * @throws Exception\InvalidOperationException
     * @throws InvalidArgumentException
     */
    final protected function parseNestedProperties($propertyName)
    {
        $parts      = explode('.', $propertyName);
        $partsCount = count($parts);

        $properties = [];
        $class      = $this->class;
        $nullable   = false;

        foreach ($parts as $key => $propertyPart) {
            $property = $class->getProperty($propertyPart);
            if ($nullable) {
                $property = $property->asNullable();
            }

            $properties[] = $property;

            if ($key < $partsCount - 1) {
                if ($property->getType()->isNullable()) {
                    $nullable = true;
                }

                $type = $property->getType()->nonNullable();

                if (!($type instanceof ObjectType) || !is_subclass_of($type->getClass(), TypedObject::class, true)) {
                    throw Exception\InvalidOperationException::format(
                            'Invalid property string \'%s\': property %s::$%s must be a subclass of %s to use nested property, %s given',
                            $propertyName, $class->getClassName(), $propertyPart, TypedObject::class, $type->asTypeString()
                    );
                }

                /** @var TypedObject|string $className */
                $className = $type->getClass();
                $class     = $className::definition();
            }
        }

        return $properties;
    }
}