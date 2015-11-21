<?php

namespace Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Exception\NotImplementedException;
use Iddigital\Cms\Core\Model\Criteria\Condition\ConditionOperator;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\EntityRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\ISeparateTableRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IToOneRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\RelationObjectReference;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\BinOp;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The to-one entity object relation mapping class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToOneEntityRelationMapping extends ToOneRelationMapping
{
    /**
     * @var IToOneRelation|ISeparateTableRelation|EntityRelation
     */
    protected $relation;

    /**
     * ToOneEntityRelationMapping constructor.
     *
     * @param IEntityMapper                                        $rootEntityMapper
     * @param IRelation[]                                          $nestedRelations
     * @param ISeparateTableRelation|IToOneRelation|EntityRelation $relation
     */
    public function __construct(IEntityMapper $rootEntityMapper, array $nestedRelations, IToOneRelation $relation)
    {
        InvalidArgumentException::verifyInstanceOf(__METHOD__, 'relation', $relation, ISeparateTableRelation::class);
        InvalidArgumentException::verifyInstanceOf(__METHOD__, 'relation', $relation, EntityRelation::class);
        InvalidArgumentException::verify(
                $relation->getReference() instanceof RelationObjectReference,
                'relation must be an id reference'
        );

        parent::__construct($rootEntityMapper, $nestedRelations, $relation);
    }

    /**
     * @inheritDoc
     */
    public function getWhereConditionExpr(Select $select, $tableAlias, $operator, $value)
    {
        $allowedOperators = [
                ConditionOperator::EQUALS,
                ConditionOperator::NOT_EQUALS,
                ConditionOperator::IN,
                ConditionOperator::NOT_IN,
        ];

        if (!in_array($operator, $allowedOperators, true)) {
            throw InvalidOperationException::format(
                    'Cannot compare entity of type %s using the \'%s\' operator, only (%s) are supported',
                    $this->getRelatedObjectType(), $operator, Debug::formatValues($allowedOperators)
            );
        }

        return $this->loadExpressionWithNecessarySubselects($select, $tableAlias,
                function (Select $select, $tableAlias) use ($operator, $value) {
                    return $this->loadWhereConditionExpr($tableAlias, $operator, $value);
                });
    }

    protected function loadWhereConditionExpr($tableAlias, $operator, $value)
    {
        if ($operator === ConditionOperator::NOT_IN) {
            return Expr::not($this->loadWhereConditionExpr($tableAlias, ConditionOperator::IN, $value));
        } elseif ($operator === ConditionOperator::IN) {
            $conditions = [];

            foreach ($value as $entity) {
                $conditions[] = $this->loadWhereConditionExpr($tableAlias, ConditionOperator::EQUALS, $entity);
            }

            return Expr::compoundAnd($conditions);
        }

        $entityType = $this->getRelatedObjectType();

        if ($value === null) {
            $idValue = null;
        } elseif ($value instanceof $entityType) {
            /** @var IEntity $value */
            if ($value->hasId()) {
                $idValue = $value->getId();
            } else {
                $idValue = false;
            }
        } else {
            throw InvalidArgumentException::format(
                    'Invalid comparison value, expecting type %s, %s given',
                    $entityType . '|' . 'null', Debug::getType($value)
            );
        }

        if ($idValue === false) {
            if ($operator === ConditionOperator::EQUALS) {
                // The entity has no id, it has not been persisted yet,
                // hence it will never equal the value in the column
                return Expr::false();
            } else {
                // Implies $operator == ConditionOperator::NOT_EQUALS,
                // as above if the entity has no id, this will always be true
                return Expr::true();
            }
        } elseif ($idValue === null) {
            return Expr::isNull(Expr::column($tableAlias, $this->relation->getRelatedPrimaryKey()));
        } else {
            return new BinOp(
                    Expr::column($tableAlias, $this->relation->getRelatedPrimaryKey()),
                    $this->mapConditionOperator($operator),
                    Expr::idParam($idValue)
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function addOrderByToSelect(Select $select, $tableAlias, $isAsc)
    {
        throw InvalidOperationException::format('Cannot order by entity of type %s', $this->getRelatedObjectType());
    }

    /**
     * @inheritDoc
     */
    public function addSelectColumn(Select $select, $tableAlias, $alias)
    {
        throw InvalidOperationException::format('Cannot select an entity of type %s as a column', $this->getRelatedObjectType());
    }

    /**
     * @inheritDoc
     */
    protected function getSingleValueExpressionInSelect(Select $select, $tableAlias)
    {
        throw NotImplementedException::method(__METHOD__);
    }
}