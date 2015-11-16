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
     * @var MemberOrdering[]
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
    final public function hasOrderings()
    {
        return count($this->orderings) > 0;
    }

    /**
     * {@inheritDoc}
     */
    final public function getOrderings()
    {
        return $this->orderings;
    }

    /**
     * Adds an ordering to the criteria.
     *
     * Example:
     * <code>
     * ->orderBy('some.property', OrderingDirection::ASC)
     * </code>
     *
     * @param string $memberExpression
     * @param string $direction
     *
     * @return static
     * @throws InvalidArgumentException
     */
    final public function orderBy($memberExpression, $direction)
    {
        $this->orderings[] = new MemberOrdering(
                $this->memberExpressionParser->parse($this->class, $memberExpression),
                $direction
        );

        return $this;
    }

    /**
     * Adds an ascending ordering to the criteria.
     *
     * Example:
     * <code>
     * ->orderByAsc('some.property')
     * </code>
     *
     * @param string $memberExpression
     *
     * @return static
     * @throws InvalidArgumentException
     */
    final public function orderByAsc($memberExpression)
    {
        return $this->orderBy($memberExpression, OrderingDirection::ASC);
    }

    /**
     * Adds an descending ordering to the criteria.
     *
     * Example:
     * <code>
     * ->orderByDesc('some.property')
     * </code>
     *
     * @param string $memberExpression
     *
     * @return static
     * @throws InvalidArgumentException
     */
    final public function orderByDesc($memberExpression)
    {
        return $this->orderBy($memberExpression, OrderingDirection::DESC);
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