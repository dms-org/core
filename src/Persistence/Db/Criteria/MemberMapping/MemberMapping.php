<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria\MemberMapping;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Persistence\Db\Criteria\MemberExpressionMapper;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\ISeparateTableRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IToOneRelation;
use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Clause\Ordering;
use Dms\Core\Persistence\Db\Query\Expression\BinOp;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Schema\Type\Type;
use Dms\Core\Util\Debug;

/**
 * The member mapping base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class MemberMapping
{
    /**
     * @var IEntityMapper
     */
    protected $rootEntityMapper;

    /**
     * @var IRelation[]
     */
    protected $relationsToSubSelect = [];

    /**
     * MemberMapping constructor.
     *
     * @param IEntityMapper $rootEntityMapper
     * @param IRelation[]   $relationsToSubSelect
     */
    public function __construct(IEntityMapper $rootEntityMapper, array $relationsToSubSelect)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'nestedRelations', $relationsToSubSelect, IRelation::class);
        $this->rootEntityMapper     = $rootEntityMapper;

        foreach ($relationsToSubSelect as $relation) {
            if ($relation->getIdString() !== MemberExpressionMapper::SELF_RELATION_ID) {
                $this->relationsToSubSelect[] = $relation;
            }
        }
    }

    /**
     * @return IEntityMapper
     */
    public function getRootEntityMapper() : IEntityMapper
    {
        return $this->rootEntityMapper;
    }

    /**
     * @return IRelation[]
     */
    public function getRelationsToSubSelect() : array
    {
        return $this->relationsToSubSelect;
    }

    /**
     * @param int $relationsToRemove
     *
     * @return static
     */
    public function withoutRelationsToSubSelect(int $relationsToRemove)
    {
        $clone                       = clone $this;
        $clone->relationsToSubSelect = array_slice($clone->relationsToSubSelect, $relationsToRemove);

        return $clone;
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
    public function getWhereConditionExpr(Select $select, string $tableAlias, string $operator, $value) : Expr
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
        } elseif ($operator === ConditionOperator::IN || $operator == ConditionOperator::NOT_IN) {
            return new BinOp(
                    $operand,
                    $this->mapConditionOperator($operator),
                    Expr::tupleParams(null, $value)
            );
        } else {
            return new BinOp(
                    $operand,
                    $this->mapConditionOperator($operator),
                    Expr::param(null, $value)
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
    protected function mapConditionOperator(string $operator) : string
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
    public function addOrderByToSelect(Select $select, string $tableAlias, bool $isAsc)
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
    public function addSelectColumn(Select $select, string $tableAlias, string $alias)
    {
        $select->addColumn($alias, $this->getExpressionInSelect($select, $tableAlias));
    }

    /**
     * @param Select $select
     * @param string $tableAlias
     *
     * @return Expr
     * @throws InvalidOperationException
     */
    protected function getExpressionInSelect(Select $select, string $tableAlias) : Expr
    {
        return $this->loadExpressionWithNecessarySubselects($select, $tableAlias, function (Select $select, $tableAlias) {
            return $this->getSingleValueExpressionInSelect($select, $tableAlias);
        });
    }

    /**
     * @param Select $select
     * @param string $tableAlias
     *
     * @return Expr
     */
    abstract protected function getSingleValueExpressionInSelect(Select $select, string $tableAlias) : Expr;

    /**
     * @return ISeparateTableRelation[]
     */
    public function getSeperateTableRelations() : array
    {
        /** @var ISeparateTableRelation[] $separateTableRelations */
        $separateTableRelations = [];

        foreach ($this->relationsToSubSelect as $nestedRelation) {
            if ($nestedRelation instanceof ISeparateTableRelation) {
                $separateTableRelations[] = $nestedRelation;
            }
        }

        return $separateTableRelations;
    }

    /**
     * @param Select   $select
     * @param string   $tableAlias
     * @param callable $expressionCallback
     *
     * @return Expr
     */
    protected function loadExpressionWithNecessarySubselects(Select $select, string $tableAlias, callable $expressionCallback) : Expr
    {
        $separateTableRelations = $this->getSeperateTableRelations();

        if ($separateTableRelations) {
            return $this->getExpressionByJoiningRelations($select, $tableAlias, $separateTableRelations, $expressionCallback);
        } else {
            return $expressionCallback($select, $tableAlias);
        }
    }

    /**
     * @param Select                   $select
     * @param string                   $tableAlias
     * @param ISeparateTableRelation[] $separateTableRelations
     * @param callable                 $expressionLoader
     *
     * @return Expr
     */
    protected function getExpressionByJoiningRelations(
            Select $select,
            string $tableAlias,
            array $separateTableRelations,
            callable $expressionLoader
    ) : Expr
    {
        /** @var Select $subSelect */
        list($subSelect, $joinedTableAlias) = $this->getJoinedSubSelectAndTableAlias($select, $tableAlias, $separateTableRelations);

        $subSelect->setColumns(['__single_val' => $expressionLoader($subSelect, $joinedTableAlias)]);

        return Expr::subSelect($subSelect);
    }

    /**
     * @param Select                   $select
     * @param string                   $tableAlias
     * @param ISeparateTableRelation[] $separateTableRelations
     *
     * @return array
     */
    protected function getJoinedSubSelectAndTableAlias(Select $select, string $tableAlias, array $separateTableRelations) : array
    {
        InvalidArgumentException::verifyAllInstanceOf(
                __METHOD__, 'separateTableRelations', $separateTableRelations, ISeparateTableRelation::class
        );

        /** @var ISeparateTableRelation $firstRelation */
        $firstRelation = array_shift($separateTableRelations);
        $subSelect     = $firstRelation->getRelationSubSelect($select, $tableAlias);

        $tableAlias = $subSelect->getTableAlias();

        foreach ($separateTableRelations as $relation) {
            if ($relation instanceof IToOneRelation) {
                $joinType = Join::LEFT;
            } else {
                $joinType = Join::INNER;
            }

            $tableAlias = $relation->joinSelectToRelatedTable(
                    $tableAlias,
                    $joinType,
                    $subSelect
            );
        }
        
        return [$subSelect, $tableAlias];
    }
}