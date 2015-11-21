<?php

namespace Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Model\Criteria\Condition\ConditionOperator;
use Iddigital\Cms\Core\Persistence\Db\Query\Clause\Ordering;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\BinOp;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The member mapping node class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class MemberMappingNode
{
    /**
     * @var MemberMappingNode[]
     */
    protected $children;

    /**
     * MemberMappingNode constructor.
     *
     * @param MemberMappingNode[] $children
     */
    public function __construct(array $children)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'children', $children, MemberMappingNode::class);

        $this->children = $children;
    }

    /**
     * @return MemberMappingNode[]
     */
    final public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param Select $select
     * @param string $tableAlias
     * @param string $operator
     * @param mixed  $value
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function addWhereConditionToSelect(Select $select, $tableAlias, $operator, $value)
    {
        $select->where($this->getWhereConditionExpr($select, $tableAlias, $operator, $value));
    }

    /**
     * @param Select $select
     * @param string $tableAlias
     * @param string $operator
     * @param mixed  $value
     *
     * @return Expr
     * @throws InvalidArgumentException
     */
    public function getWhereConditionExpr(Select $select, $tableAlias, $operator, $value)
    {
        $operand = $this->getExpressionInSelect($select, $tableAlias);

        if ($value === null) {
            if ($operator === ConditionOperator::EQUALS) {
                return Expr::isNull($operand);
            } elseif ($operator === ConditionOperator::NOT_EQUALS) {
                return Expr::isNotNull($operand);
            } else {
                throw InvalidArgumentException::format(
                        'Cannot use operator \'%s\' with NULL value, only (%s) are supported',
                        $operator, Debug::formatValues([ConditionOperator::EQUALS, ConditionOperator::NOT_EQUALS])
                );
            }
        } else {
            return new BinOp(
                    $operand,
                    $operator,
                    Expr::param($operand->getResultingType(), $value)
            );
        }
    }

    /**
     * Maps the {@see ConditionOperator} to a {@see BinOp} operator.
     *
     * @param string $operator
     *
     * @return string
     */
    protected function mapConditionOperator($operator)
    {
        ConditionOperator::validate($operator);

        static $dbOperatorMap = [
                ConditionOperator::EQUALS                           => BinOp::EQUAL,
                ConditionOperator::NOT_EQUALS                       => BinOp::NOT_EQUAL,
                ConditionOperator::IN                               => BinOp::IN,
                ConditionOperator::NOT_IN                           => BinOp::NOT_IN,
                ConditionOperator::LESS_THAN                        => BinOp::LESS_THAN,
                ConditionOperator::LESS_THAN_OR_EQUAL               => BinOp::LESS_THAN_OR_EQUAL,
                ConditionOperator::GREATER_THAN                     => BinOp::GREATER_THAN,
                ConditionOperator::GREATER_THAN_OR_EQUAL            => BinOp::GREATER_THAN_OR_EQUAL,
                ConditionOperator::STRING_CONTAINS                  => BinOp::STR_CONTAINS,
                ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE => BinOp::STR_CONTAINS_CASE_INSENSITIVE,
        ];

        return $dbOperatorMap[$operator];
    }

    /**
     * @param Select $select
     * @param string $tableAlias
     * @param bool   $isAsc
     *
     * @return void
     * @throws InvalidOperationException
     */
    public function addOrderByToSelect(Select $select, $tableAlias, $isAsc)
    {
        $this->addOrderBy($select, $this->getExpressionInSelect($select, $tableAlias), $isAsc);
    }

    protected function addOrderBy(Select $select, Expr $orderByExpression, $isAsc)
    {
        $select->orderBy(new Ordering(
                $orderByExpression,
                $isAsc ? Ordering::ASC : Ordering::DESC
        ));
    }

    /**
     * @param Select $select
     * @param string $tableAlias
     * @param string $alias
     *
     * @return void
     * @throws InvalidOperationException
     */
    public function addSelectColumn(Select $select, $tableAlias, $alias)
    {
        $select->addColumn($alias, $this->getExpressionInSelect($select, $tableAlias));
    }

    /**
     * @param Select $select
     * @param string $parentTableAlias
     *
     * @return string The joined table alias
     * @throws InvalidOperationException
     */
    abstract public function applyLeftOneToOneJoinsToSelect(Select $select, $parentTableAlias);

    /**
     * @param Select $select
     * @param string $tableAlias
     *
     * @return Expr
     * @throws InvalidOperationException
     */
    abstract protected function getExpressionInSelect(Select $select, $tableAlias);
}