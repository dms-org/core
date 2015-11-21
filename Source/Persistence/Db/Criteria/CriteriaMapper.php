<?php

namespace Iddigital\Cms\Core\Persistence\Db\Criteria;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\Criteria\Condition\AndCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\Condition;
use Iddigital\Cms\Core\Model\Criteria\Condition\InstanceOfCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\MemberCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\NotCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\OrCondition;
use Iddigital\Cms\Core\Model\Criteria\Criteria;
use Iddigital\Cms\Core\Model\Criteria\MemberOrdering;
use Iddigital\Cms\Core\Model\Criteria\NestedMember;
use Iddigital\Cms\Core\Model\ICriteria;
use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\ArrayReadModelMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\ISeparateTableRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IToManyRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IToOneRelation;
use Iddigital\Cms\Core\Persistence\Db\Query;
use Iddigital\Cms\Core\Persistence\Db\Query\Clause\Join;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The criteria mapper class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CriteriaMapper
{
    /**
     * @var IEntityMapper
     */
    private $mapper;

    /**
     * @var FinalizedMapperDefinition
     */
    private $definition;

    /**
     * @var MemberExpressionMapper
     */
    private $memberExpressionMapper;

    /**
     * @var Table
     */
    private $primaryTable;

    /**
     * CriteriaMapper constructor.
     *
     * @param IEntityMapper $mapper
     */
    public function __construct(IEntityMapper $mapper)
    {
        $mapper->initializeRelations();

        $this->mapper                 = $mapper;
        $this->definition             = $this->mapper->getDefinition();
        $this->memberExpressionMapper = new MemberExpressionMapper($mapper);
        $this->primaryTable           = $this->mapper->getDefinition()->getTable();
    }

    /**
     * @return IObjectMapper
     */
    final public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @return FinalizedClassDefinition
     */
    protected function getMappedObjectType()
    {
        if ($this->mapper instanceof ArrayReadModelMapper) {
            return $this->mapper->getParentMapper()->getDefinition()->getClass();
        } else {
            return $this->mapper->getDefinition()->getClass();
        }
    }

    /**
     * @return Criteria
     */
    public function newCriteria()
    {
        return new Criteria($this->getMappedObjectType());
    }

    /**
     * Maps the supplied criteria to a select query for the entity.
     *
     * @param ICriteria $criteria
     *
     * @return Select
     */
    public function mapCriteriaToSelect(ICriteria $criteria)
    {
        $criteria->verifyOfClass($this->getMappedObjectType()->getClassName());

        $select = Select::from($this->primaryTable);

        $memberMappings = $this->mapAllRequiredMembersFor($criteria, $select->getTableAlias());

        $condition = null;
        $loaded    = false;

        if ($criteria->hasCondition()) {
            $condition = $this->applySpecificInstanceOfCondition(
                    $criteria->getCondition(),
                    $select,
                    /* out */
                    $loaded
            );
        }

        if (!$loaded) {
            $this->mapper->getMapping()->addLoadToSelect($select);
        }

        $memberMappings = $this->optimizeOneToOneRelationsAsLeftJoins($select, $memberMappings);

        if ($condition) {
            $select->where($this->mapCondition($condition, $select, $memberMappings));
        }

        foreach ($criteria->getOrderings() as $ordering) {
            $this->mapOrdering($ordering, $select, $memberMappings);
        }

        $select->offset($criteria->getStartOffset())
                ->limit($criteria->getLimitAmount());

        return $select;
    }

    /**
     * @param ICriteria $criteria
     * @param string    $tableAlias
     *
     * @return MemberMappingWithTableAlias[]
     * @throws MemberExpressionMappingException
     */
    private function mapAllRequiredMembersFor(ICriteria $criteria, $tableAlias)
    {
        /** @var NestedMember[] $memberExpressions */
        $memberExpressions = [];

        if ($criteria->hasCondition()) {
            $criteria->getCondition()->walkRecursive(function (Condition $condition) use (&$memberExpressions) {
                if ($condition instanceof MemberCondition) {
                    $member                                 = $condition->getNestedMember();
                    $memberExpressions[$member->asString()] = $member;
                }
            });
        }

        foreach ($criteria->getOrderings() as $ordering) {
            $member                                 = $ordering->getNestedMember();
            $memberExpressions[$member->asString()] = $member;
        }

        /** @var MemberMappingWithTableAlias[] $memberMappings */
        $memberMappings = [];

        foreach ($memberExpressions as $key => $member) {
            $memberMappings[$key] = new MemberMappingWithTableAlias(
                    $this->memberExpressionMapper->mapMemberExpression($member),
                    $tableAlias
            );
        }

        return $memberMappings;
    }

    /**
     * @param Select                        $select
     * @param MemberMappingWithTableAlias[] $memberMappings
     *
     * @return MemberMappingWithTableAlias[]
     */
    private function optimizeOneToOneRelationsAsLeftJoins(Select $select, array $memberMappings)
    {
        $joinedRelationTableAliasMap = new \SplObjectStorage();

        foreach ($memberMappings as $key => $mappingWithAlias) {
            $mapping = $mappingWithAlias->getMapping();

            $parentTableAlias  = $select->getTableAlias();
            $relationsToRemove = 0;

            foreach ($mapping->getNestedRelations() as $relation) {

                if ($relation instanceof IToOneRelation && $relation instanceof ISeparateTableRelation) {
                    if (isset($joinedRelationTableAliasMap[$relation])) {
                        $parentTableAlias = $joinedRelationTableAliasMap[$relation];
                    } else {
                        $parentTableAlias = $relation->joinSelectToRelatedTable($parentTableAlias, Join::LEFT, $select);

                        $joinedRelationTableAliasMap[$relation] = $parentTableAlias;
                    }
                } elseif ($relation instanceof IToManyRelation) {
                    break;
                }

                $relationsToRemove++;
            }

            $memberMappings[$key] = new MemberMappingWithTableAlias(
                    $mapping->withRelations(array_slice($mapping->getNestedRelations(), $relationsToRemove)),
                    $parentTableAlias
            );
        }

        return $memberMappings;
    }

    /**
     * @param Condition $condition
     * @param Select    $select
     * @param bool      &$loaded
     *
     * @return Condition|null
     */
    private function applySpecificInstanceOfCondition(Condition $condition, Select $select, &$loaded)
    {
        if ($condition instanceof InstanceOfCondition) {
            $class = $condition->getClass();

            if ($class === $this->mapper->getObjectType()) {
                return null;
            }

            $this->mapper->getMapping()->addSpecificLoadToQuery($select, $class);
            $loaded = true;

            return null;
        } elseif ($condition instanceof AndCondition) {
            $innerConditions = $condition->getConditions();

            foreach ($innerConditions as $key => $condition) {
                $innerConditions[$key] = $this->applySpecificInstanceOfCondition($condition, $select, $loaded);
            }

            $innerConditions = array_filter($innerConditions);

            return count($innerConditions) === 1 ? reset($innerConditions) : new AndCondition($innerConditions);
        } else {
            return $condition;
        }
    }

    /**
     * @param Condition                     $condition
     * @param Select                        $select
     * @param MemberMappingWithTableAlias[] $memberMappings
     *
     * @return Expr
     * @throws InvalidArgumentException
     */
    private function mapCondition(Condition $condition, Select $select, array $memberMappings)
    {
        /** @var MemberMappingWithTableAlias[] $memberMappings */

        if ($condition instanceof AndCondition) {
            $expressions = [];
            foreach ($condition->getConditions() as $condition) {
                $expressions[] = $this->mapCondition($condition, $select, $memberMappings);
            }

            return Expr::compoundAnd($expressions);
        } elseif ($condition instanceof OrCondition) {
            $expressions = [];
            foreach ($condition->getConditions() as $condition) {
                $expressions[] = $this->mapCondition($condition, $select, $memberMappings);
            }

            return Expr::compoundOr($expressions);
        } elseif ($condition instanceof MemberCondition) {
            return $this->mapMemberCondition($condition, $select, $memberMappings);
        } elseif ($condition instanceof InstanceOfCondition) {
            return $this->mapper->getMapping()->getClassConditionExpr($select, $condition->getClass());
        } elseif ($condition instanceof NotCondition) {
            return Expr::not($this->mapCondition($condition->getCondition(), $select, $memberMappings));
        }

        throw InvalidArgumentException::format(
                'Unknown condition type %s', get_class($condition)
        );
    }

    /**
     * @param MemberOrdering                $ordering
     * @param Select                        $select
     * @param MemberMappingWithTableAlias[] $memberMappings
     *
     * @return void
     */
    private function mapOrdering(MemberOrdering $ordering, Select $select, array $memberMappings)
    {
        $mapping = $memberMappings[$ordering->getNestedMember()->asString()];

        $mapping->getMapping()
                ->addOrderByToSelect($select, $mapping->getTableAlias(), $ordering->isAsc());
    }

    /**
     * @param MemberCondition               $condition
     * @param Select                        $select
     * @param MemberMappingWithTableAlias[] $memberMappings
     *
     * @return Expr
     */
    private function mapMemberCondition(MemberCondition $condition, Select $select, array $memberMappings)
    {
        $mapping = $memberMappings[$condition->getNestedMember()->asString()];

        return $mapping->getMapping()
                ->getWhereConditionExpr($select, $mapping->getTableAlias(), $condition->getOperator(), $condition->getValue());
    }
}