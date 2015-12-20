<?php

namespace Dms\Core\Model\Type;

use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Model\IComparable;

/**
 * The object type class.
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectType extends BaseType
{
    /**
     * @var string|null
     */
    private $class;

    public function __construct($class = null)
    {
        parent::__construct($class ?: 'object');

        $this->class = $class;
    }

    /**
     * @param IType $type
     *
     * @return IType|null
     */
    protected function intersection(IType $type)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    protected function loadValidOperatorTypes()
    {
        $operators = parent::loadValidOperatorTypes();

        if ($this->class) {
            if (is_a($this->class, \DateTimeInterface::class, true)
                || is_a($this->class, IComparable::class, true)) {
                $objectType = $this->nullable();
                $operators += [
                        ConditionOperator::GREATER_THAN          => $objectType,
                        ConditionOperator::GREATER_THAN_OR_EQUAL => $objectType,
                        ConditionOperator::LESS_THAN             => $objectType,
                        ConditionOperator::LESS_THAN_OR_EQUAL    => $objectType,
                ];
            }
        }

        return $operators;
    }

    /**
     * @inheritDoc
     */
    protected function checkThisIsSubsetOf(IType $type)
    {
        if ($type instanceof self) {
            if (!$type->class) {
                return true;
            } elseif ($this->class) {
                return is_subclass_of($this->class, $type->class, true);
            }
        }

        return parent::checkThisIsSubsetOf($type);
    }


    /**
     * Gets the object class type or null if there is no
     * specified class.
     *
     * @return string|null
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function isOfType($value)
    {
        $class = $this->class;

        if ($class) {
            return $value instanceof $class;
        } else {
            return is_object($value);
        }
    }
}