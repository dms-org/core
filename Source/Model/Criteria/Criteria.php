<?php

namespace Iddigital\Cms\Core\Model\Criteria;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\ICriteria;

/**
 * The typed object criteria class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Criteria extends SpecificationDefinition implements ICriteria
{
    /**
     * @var PropertyOrdering[]
     */
    private $orderings = [];

    /**
     * @var int
     */
    private $startOffset = 0;

    /**
     * @var int|null
     */
    private $limitAmount = null;

    /**
     * {@inheritDoc}
     */
    final public function getOrderings()
    {
        return $this->orderings;
    }

    /**
     * @param string $propertyName
     * @param string $direction
     *
     * @return static
     * @throws InvalidArgumentException
     */
    final public function orderBy($propertyName, $direction)
    {
        $this->orderings[] = new PropertyOrdering(
                NestedProperty::parsePropertyName($this->class, $propertyName),
                $direction
        );

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    final public function orderByAsc($propertyName)
    {
        return $this->orderBy($propertyName, OrderingDirection::ASC);
    }

    /**
     * {@inheritDoc}
     */
    final public function orderByDesc($propertyName)
    {
        return $this->orderBy($propertyName, OrderingDirection::DESC);
    }

    /**
     * {@inheritDoc}
     */
    final public function getStartOffset()
    {
        return $this->startOffset;
    }

    /**
     * @param int $amount
     *
     * @return static
     */
    final public function skip($amount)
    {
        $this->startOffset = (int)$amount;

        return $this;
    }

    /**
     * @inheritDoc
     */
    final public function hasLimitAmount()
    {
        return $this->limitAmount !== null;
    }

    /**
     * {@inheritDoc}
     */
    final public function getLimitAmount()
    {
        return $this->limitAmount;
    }

    /**
     * @param int $amount
     *
     * @return static
     */
    final public function limit($amount)
    {
        $this->limitAmount = $amount === null ? null : (int)$amount;

        return $this;
    }
}