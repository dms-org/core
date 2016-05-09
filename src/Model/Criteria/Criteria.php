<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria;

use Dms\Core\Exception;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Criteria\Condition\AndCondition;
use Dms\Core\Model\ICriteria;
use Dms\Core\Model\ISpecification;

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
    final public function hasOrderings() : bool
    {
        return count($this->orderings) > 0;
    }

    /**
     * {@inheritDoc}
     */
    final public function getOrderings() : array
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
    final public function orderBy(string $memberExpression, string $direction)
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
    final public function orderByAsc(string $memberExpression)
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
    final public function orderByDesc(string $memberExpression)
    {
        return $this->orderBy($memberExpression, OrderingDirection::DESC);
    }

    /**
     * {@inheritDoc}
     */
    final public function getStartOffset() : int
    {
        return $this->startOffset;
    }

    /**
     * @param int $amount
     *
     * @return static
     */
    final public function skip(int $amount)
    {
        $this->startOffset = (int)$amount;

        return $this;
    }

    /**
     * @inheritDoc
     */
    final public function hasLimitAmount() : bool
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
     * @param int|null $amount
     *
     * @return static
     */
    final public function limit(int $amount = null)
    {
        $this->limitAmount = $amount === null ? null : $amount;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function merge(ICriteria $criteria) : ICriteria
    {
        $criteria->verifyOfClass($this->class->getClassName());

        $mergedCriteria = $this->asMutableCriteria();

        if ($criteria->hasCondition() && $this->hasCondition()) {
            $mergedCriteria->condition = new AndCondition([
                $this->condition, $criteria->getCondition(),
            ]);
        } elseif ($criteria->hasCondition() && !$this->hasCondition()) {
            $mergedCriteria->condition = $criteria->getCondition();
        }

        $mergedCriteria->orderings = array_merge($criteria->getOrderings(), $this->orderings);
        $mergedCriteria->startOffset += $criteria->getStartOffset();

        if ($criteria->hasLimitAmount() && $this->hasLimitAmount()) {
            $mergedCriteria->limitAmount = min($criteria->getLimitAmount(), $this->limitAmount);
        } elseif ($criteria->hasLimitAmount() && !$this->hasLimitAmount()) {
            $mergedCriteria->limitAmount = $criteria->getLimitAmount();
        }

        return $mergedCriteria;
    }


    /**
     * Returns a copy of the criteria which can be modified.
     *
     * @return Criteria
     */
    public function asMutableCriteria() : Criteria
    {
        return clone $this;
    }

    /**
     * @inheritDoc
     */
    public function asSpecification() : ISpecification
    {
        return new CustomSpecification($this->class->getClassName(), function (SpecificationDefinition $match) {
            $match->condition = $this->condition;
        });
    }
}