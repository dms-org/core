<?php

namespace Dms\Core\Model\Criteria;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Object\FinalizedPropertyDefinition;
use Dms\Core\Model\Type\IType;

/**
 * The nested member expression class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NestedMember
{
    /**
     * @var IMemberExpression[]
     */
    protected $parts;

    /**
     * @var string
     */
    protected $expressionString;

    /**
     * PropertyCriterion constructor.
     *
     * @param IMemberExpression[] $parts
     */
    public function __construct(array $parts)
    {
        InvalidArgumentException::verify(!empty($parts), 'parts cannot be empty');
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'parts', $parts, IMemberExpression::class);

        $this->parts = $parts;

        $names = [];

        foreach ($this->parts as $part) {
            $names[] = $part->asString();
        }

        $this->expressionString = implode('.', $names);
    }

    /**
     * @return IMemberExpression[]
     */
    final public function getParts()
    {
        return $this->parts;
    }

    /**
     * @return IMemberExpression[]
     */
    final public function getPartsExceptLast()
    {
        return array_slice($this->parts, 0, -1);
    }

    /**
     * @return IMemberExpression
     */
    final public function getLastPart()
    {
        return end($this->parts);
    }

    /**
     * @return IType
     */
    final public function getResultingType()
    {
        return $this->getLastPart()->getResultingType();
    }

    /**
     * @return bool
     */
    final public function isPropertyValue()
    {
        return $this->getLastPart()->isPropertyValue();
    }

    /**
     * @return FinalizedPropertyDefinition
     */
    final public function getProperty()
    {
        return $this->getLastPart()->getProperty();
    }

    /**
     * @return string
     */
    final public function asString()
    {
        return $this->expressionString;
    }

    /**
     * NOTE: keys are maintained
     *
     * @return \Closure
     */
    final public function makeArrayGetterCallable()
    {
        $getters = [];

        foreach ($this->parts as $part) {
            $getters[] = $part->createArrayGetterCallable();
        }

        return function (array $objects) use ($getters) {
            $results = $objects;

            foreach ($getters as $getter) {
                $results = $getter($results);
            }

            return $results;
        };
    }
}