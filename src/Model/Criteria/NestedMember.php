<?php declare(strict_types = 1);

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
    final public function getParts() : array
    {
        return $this->parts;
    }

    /**
     * @return IMemberExpression[]
     */
    final public function getPartsExceptLast() : array
    {
        return array_slice($this->parts, 0, -1);
    }

    /**
     * @return IMemberExpression
     */
    final public function getLastPart() : IMemberExpression
    {
        return end($this->parts);
    }

    /**
     * @return IType
     */
    final public function getResultingType() : IType
    {
        return $this->getLastPart()->getResultingType();
    }

    /**
     * @return bool
     */
    final public function isPropertyValue() : bool
    {
        return $this->getLastPart()->isPropertyValue();
    }

    /**
     * @return FinalizedPropertyDefinition
     */
    final public function getProperty() : FinalizedPropertyDefinition
    {
        return $this->getLastPart()->getProperty();
    }

    /**
     * @return string
     */
    final public function asString() : string
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

    /**
     * @param NestedMember $member
     *
     * @return NestedMember
     */
    public function merge(NestedMember $member) : NestedMember
    {
        $parts = $this->parts;

        $isNullable = $this->getResultingType()->isNullable();
        foreach ($member->getParts() as $part) {
            $parts[] = $isNullable ? $part->asNullable() : $part;
        }

        return new self($parts);
    }
}