<?php

namespace Iddigital\Cms\Core\Model\Criteria\Condition;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Model\Criteria\NestedProperty;
use Iddigital\Cms\Core\Model\Object\FinalizedPropertyDefinition;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The property condition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PropertyCondition extends Condition
{
    /**
     * @var NestedProperty
     */
    private $nestedProperty;

    /**
     * @var string
     */
    private $conditionOperator;

    /**
     * @var mixed
     */
    private $value;

    /**
     * PropertyCondition constructor.
     *
     * @param NestedProperty $property
     * @param string         $conditionOperator
     * @param mixed          $value
     *
     * @throws InvalidArgumentException
     * @throws TypeMismatchException
     */
    final public function __construct(NestedProperty $property, $conditionOperator, $value)
    {
        $lastPropertyType = $property->getResultingType();

        $operators = $lastPropertyType->getConditionOperatorTypes();

        if (!isset($operators[$conditionOperator])) {
            throw InvalidArgumentException::format(
                    'Invalid condition operator for property of type %s: expecting one of (%s), %s given',
                    $lastPropertyType->asTypeString(), Debug::formatValues(array_keys($operators)), $conditionOperator
            );
        }

        $valueType = $operators[$conditionOperator]->getValueType();
        if (!$valueType->isOfType($value)) {
            throw TypeMismatchException::argument(__METHOD__, 'value', $valueType->asTypeString(), $value);
        }

        ConditionOperator::validate($conditionOperator);

        $this->nestedProperty    = $property;
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
     * @return FinalizedPropertyDefinition[]
     */
    final public function getNestedProperties()
    {
        return $this->nestedProperty->getNestedProperties();
    }

    protected function makeFilterCallable()
    {
        $getter = $this->nestedProperty->makePropertyGetterCallable();
        $value  = $this->value;

        return ConditionOperator::makeOperatorCallable(
                $getter,
                $this->conditionOperator,
                function () use ($value) {
                    return $value;
                }
        );
    }
}