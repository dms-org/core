<?php

namespace Iddigital\Cms\Core\Model\Criteria;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Model\ITypedObject;
use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;
use Iddigital\Cms\Core\Model\Object\FinalizedPropertyDefinition;
use Iddigital\Cms\Core\Model\Object\TypedObject;
use Iddigital\Cms\Core\Model\Type\IType;
use Iddigital\Cms\Core\Model\Type\ObjectType;

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
     * PropertyCriterion constructor.
     *
     * @param IMemberExpression[] $parts
     */
    public function __construct(array $parts)
    {
        InvalidArgumentException::verify(!empty($parts), 'parts cannot be empty');
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'parts', $parts, IMemberExpression::class);

        $this->parts = $parts;
    }

    /**
     * @return IMemberExpression[]
     */
    final public function getParts()
    {
        return $this->parts;
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
        $names = [];

        foreach ($this->parts as $part) {
            $names[] = $part->asString();
        }

        return implode('.', $names);
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