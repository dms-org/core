<?php

namespace Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping;

use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\EntityRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Iddigital\Cms\Core\Persistence\Db\Query\Clause\Ordering;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\BinOp;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;

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
    protected $nestedRelations;

    /**
     * MemberMapping constructor.
     *
     * @param IEntityMapper $rootEntityMapper
     * @param IRelation[]   $nestedRelations
     */
    public function __construct(IEntityMapper $rootEntityMapper, array $nestedRelations)
    {
        $this->rootEntityMapper = $rootEntityMapper;
        $this->nestedRelations  = $nestedRelations;
    }

    /**
     * @return IEntityMapper
     */
    public function getRootEntityMapper()
    {
        return $this->rootEntityMapper;
    }

    /**
     * @return IRelation[]
     */
    public function getNestedRelations()
    {
        return $this->nestedRelations;
    }

    /**
     * @param Select $select
     * @param string $operator
     * @param mixed  $value
     *
     * @return void
     * @throws InvalidOperationException
     */
    public function addWhereConditionToSelect(Select $select, $operator, $value)
    {
        $operand = $this->getSingleValueExpression($select);
        $select->where(new BinOp(
                $operand,
                $operator,
                Expr::param($operand->getResultingType(), $value)
        ));
    }

    /**
     * @param Select $select
     * @param bool   $isAsc
     *
     * @return void
     * @throws InvalidOperationException
     */
    public function addOrderByToSelect(Select $select, $isAsc)
    {
        $select->orderBy(new Ordering(
                $this->getSingleValueExpression($select),
                $isAsc ? Ordering::ASC : Ordering::DESC
        ));
    }

    /**
     * @param Select $select
     * @param string $alias
     *
     * @return void
     * @throws InvalidOperationException
     */
    public function addSelectColumn(Select $select, $alias)
    {
        $select->addColumn($alias, $this->getSingleValueExpression($select));
    }

    /**
     * @param Select $select
     *
     * @return Expr
     * @throws InvalidOperationException
     */
    protected function getSingleValueExpression(Select $select)
    {
        if ($this->nestedRelations) {
            return $this->getExpressionInRelated($select, $this->nestedRelations);
        } else {
            return $this->getExpressionInSelect($select);
        }
    }

    /**
     * @param Select $select
     *
     * @return Expr
     */
    abstract protected function getExpressionInSelect(Select $select);

    /**
     * @param Select      $select
     * @param IRelation[] $nestedRelations
     *
     * @return Expr
     */
    protected function getExpressionInRelated(Select $select, array $nestedRelations)
    {
        /** @var EntityRelation[] $entityRelations */
        $entityRelations = [];

        foreach ($nestedRelations as $nestedRelation) {
            if ($nestedRelation instanceof EntityRelation) {
                $entityRelations[] = $nestedRelation;
            }
        }

        if (!$entityRelations) {
            return $this->getExpressionInSelect($select);
        }

        $firstEntityRelation = array_shift($entityRelations);
        $subSelect           = new Select($firstEntityRelation->getEntityMapper()->getPrimaryTable());
        // TODO: where and joins
    }
}