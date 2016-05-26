<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria\MemberMapping;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Model\IEntity;
use Dms\Core\Persistence\Db\Criteria\MemberExpressionMappingException;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Relation\MemberRelation;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Relation\ToOneMemberRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\EntityRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\ISeparateTableRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IToOneRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\RelationObjectReference;
use Dms\Core\Persistence\Db\Query\Expression\BinOp;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Util\Debug;

/**
 * The to-one entity object relation mapping class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToOneEntityRelationMapping extends ToOneRelationMapping implements IFinalRelationMemberMapping
{
    /**
     * ToOneEntityRelationMapping constructor.
     *
     * @param IEntityMapper                                        $rootEntityMapper
     * @param IRelation[]                                          $relationsToSubSelect
     * @param ISeparateTableRelation|IToOneRelation|EntityRelation $relation
     */
    public function __construct(IEntityMapper $rootEntityMapper, array $relationsToSubSelect, IToOneRelation $relation)
    {
        InvalidArgumentException::verifyInstanceOf(__METHOD__, 'relation', $relation, ISeparateTableRelation::class);
        InvalidArgumentException::verifyInstanceOf(__METHOD__, 'relation', $relation, EntityRelation::class);
        InvalidArgumentException::verify(
                $relation->getReference() instanceof RelationObjectReference,
                'relation must be an id reference'
        );

        parent::__construct($rootEntityMapper, $relationsToSubSelect, $relation);
    }

    /**
     * @return IToOneRelation|ISeparateTableRelation|EntityRelation
     */
    public function getRelation() : IToOneRelation
    {
        return parent::getRelation();
    }

    /**
     * @inheritDoc
     */
    public function asMemberRelation() : MemberRelation
    {
        return new ToOneMemberRelation($this);
    }

    /**
     * @inheritDoc
     */
    public function getWhereConditionExpr(Select $select, string $tableAlias, string $operator, $value) : Expr
    {
        $allowedOperators = [
                ConditionOperator::EQUALS,
                ConditionOperator::NOT_EQUALS,
                ConditionOperator::IN,
                ConditionOperator::NOT_IN,
        ];

        if (!in_array($operator, $allowedOperators, true)) {
            throw MemberExpressionMappingException::format(
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

            return Expr::compoundOr($conditions);
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

        $relatedPrimaryKey = $this->getRelation()->getRelatedPrimaryKey();

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
            return $operator === ConditionOperator::EQUALS
                    ? Expr::isNull(Expr::column($tableAlias, $relatedPrimaryKey))
                    : Expr::isNotNull(Expr::column($tableAlias, $relatedPrimaryKey));
        } else {
            return new BinOp(
                    Expr::column($tableAlias, $relatedPrimaryKey),
                    $this->mapConditionOperator($operator),
                    Expr::param(null, $idValue)
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function addOrderByToSelect(Select $select, string $tableAlias, bool $isAsc)
    {
        throw InvalidOperationException::format('Cannot order by entity of type %s', $this->getRelatedObjectType());
    }

    /**
     * @inheritDoc
     */
    public function addSelectColumn(Select $select, string $tableAlias, string $alias)
    {
        throw InvalidOperationException::format('Cannot select an entity of type %s as a column', $this->getRelatedObjectType());
    }

    /**
     * @inheritDoc
     */
    protected function getSingleValueExpressionInSelect(Select $select, string $tableAlias) : Expr
    {
        throw NotImplementedException::method(__METHOD__);
    }
}