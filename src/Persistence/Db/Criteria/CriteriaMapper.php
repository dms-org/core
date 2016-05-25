<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Criteria\Condition\AndCondition;
use Dms\Core\Model\Criteria\Condition\Condition;
use Dms\Core\Model\Criteria\Condition\InstanceOfCondition;
use Dms\Core\Model\Criteria\Condition\MemberCondition;
use Dms\Core\Model\Criteria\Condition\NotCondition;
use Dms\Core\Model\Criteria\Condition\OrCondition;
use Dms\Core\Model\Criteria\Criteria;
use Dms\Core\Model\Criteria\MemberExpressionParser;
use Dms\Core\Model\Criteria\MemberOrdering;
use Dms\Core\Model\Criteria\NestedMember;
use Dms\Core\Model\ICriteria;
use Dms\Core\Model\Object\FinalizedClassDefinition;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\IObjectMapper;
use Dms\Core\Persistence\Db\Mapping\Relation\ISeparateTableRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IToManyRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IToOneRelation;
use Dms\Core\Persistence\Db\Query;
use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Schema\Table;

/**
 * The criteria mapper class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CriteriaMapper
{
    /**
     * @var IObjectMapper
     */
    private $mapper;

    /**
     * @var IConnection|null
     */
    private $connection;

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
     * @param IEntityMapper    $mapper
     * @param IConnection|null $connection
     */
    public function __construct(IEntityMapper $mapper, IConnection $connection = null)
    {
        $mapper->initializeRelations();

        $this->mapper                 = $mapper;
        $this->connection             = $connection;
        $this->definition             = $this->mapper->getDefinition();
        $this->memberExpressionMapper = new MemberExpressionMapper($mapper);
        $this->primaryTable           = $this->mapper->getDefinition()->getTable();
    }

    /**
     * @return IEntityMapper
     */
    final public function getMapper() : IEntityMapper
    {
        return $this->mapper;
    }

    /**
     * @return IConnection|null
     */
    final public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return MemberExpressionParser
     */
    public function buildMemberExpressionParser() : MemberExpressionParser
    {
        $orm = $this->definition->getOrm();

        $memberExpressionParser = new MemberExpressionParser(
            $this->connection ? $orm->getEntityDataSourceProvider($this->connection) : null,
            $orm
        );

        return $memberExpressionParser;
    }

    /**
     * @return FinalizedClassDefinition
     */
    final public function getMappedObjectType() : FinalizedClassDefinition
    {
        return $this->mapper->getDefinition()->getClass();
    }

    /**
     * @return Criteria
     */
    public function newCriteria() : Criteria
    {
        $memberExpressionParser = $this->buildMemberExpressionParser();

        return new Criteria($this->getMappedObjectType(), $memberExpressionParser);
    }

    /**
     * Maps the supplied criteria to a select query for the entity.
     *
     * @param ICriteria                       $criteria
     * @param MemberMappingWithTableAliases[] &$memberMappings
     * @param NestedMember[]                  $extraRequiredMembers
     *
     * @return Select
     * @throws InvalidArgumentException
     */
    public function mapCriteriaToSelect(ICriteria $criteria, array &$memberMappings = null, array $extraRequiredMembers = []) : Select
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'extraRequiredMembers', $extraRequiredMembers, NestedMember::class);
        $criteria->verifyOfClass($this->getMappedObjectType()->getClassName());

        $select = Select::from($this->primaryTable);

        return $this->mapCriteriaToExistingSelect($criteria, $select, $select->getTableAlias(), $memberMappings, $extraRequiredMembers);
    }

    /**
     * Maps the supplied criteria to a delete query for the entity.
     *
     * @param ICriteria $criteria
     *
     * @return Delete
     * @throws InvalidArgumentException
     */
    public function mapCriteriaToDelete(ICriteria $criteria) : Delete
    {
        return Delete::copyFrom($this->mapCriteriaToSelect($criteria));
    }

    /**
     * Maps the supplied criteria to an existing select query for the entity.
     *
     * @param ICriteria $criteria
     * @param Select    $select
     * @param string    $tableAlias
     * @param array     $memberMappings
     * @param array     $extraRequiredMembers
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function mapCriteriaToExistingSelect(
        ICriteria $criteria,
        Select $select,
        string $tableAlias,
        array &$memberMappings = null,
        array $extraRequiredMembers = []
    ) {
        $memberMappings = $this->mapAllRequiredMembersFor($criteria, $tableAlias, $extraRequiredMembers);

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
            $this->mapper->getMapping()->addLoadToSelect($select, $tableAlias);
        }

        $memberMappings = $this->optimizeOneToOneRelationsAsLeftJoins($select, $tableAlias, $memberMappings);

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
     * @param ICriteria      $criteria
     * @param string         $tableAlias
     * @param NestedMember[] $extraRequiredMembers
     *
     * @return MemberMappingWithTableAliases[]
     * @throws MemberExpressionMappingException
     */
    private function mapAllRequiredMembersFor(ICriteria $criteria, string $tableAlias, array $extraRequiredMembers) : array
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


        foreach ($extraRequiredMembers as $member) {
            $memberExpressions[$member->asString()] = $member;
        }

        /** @var MemberMappingWithTableAliases[] $memberMappings */
        $memberMappings = [];

        foreach ($memberExpressions as $key => $member) {
            $memberMappings[$key] = new MemberMappingWithTableAliases(
                $this->memberExpressionMapper->mapMemberExpression($member),
                [$tableAlias]
            );
        }

        return $memberMappings;
    }

    /**
     * @param Select                          $select
     * @param string                          $initialTableAlias
     * @param MemberMappingWithTableAliases[] $memberMappings
     *
     * @return array|MemberMappingWithTableAliases[]
     */
    private function optimizeOneToOneRelationsAsLeftJoins(Select $select, string $initialTableAlias, array $memberMappings) : array
    {
        $joinedRelationTableAliasMap = [];

        foreach ($memberMappings as $key => $mappingWithAlias) {
            $mapping           = $mappingWithAlias->getMapping();
            $tableAliases  = [$initialTableAlias];
            $relationsToRemove = 0;

            foreach ($mapping->getRelationsToSubSelect() as $relation) {

                if ($relation instanceof IToOneRelation && $relation instanceof ISeparateTableRelation) {
                    if (isset($joinedRelationTableAliasMap[$relation->getIdString()])) {
                        $tableAliases[] = $joinedRelationTableAliasMap[$relation->getIdString()];
                    } else {
                        $tableAliases[] = $tableAlias = $relation->joinSelectToRelatedTable(end($tableAliases), Join::LEFT, $select);

                        $joinedRelationTableAliasMap[$relation->getIdString()] = $tableAlias;
                    }
                } elseif ($relation instanceof IToManyRelation) {
                    break;
                }

                $relationsToRemove++;
            }

            $memberMappings[$key] = new MemberMappingWithTableAliases(
                $mapping->withoutRelationsToSubSelect($relationsToRemove),
                $tableAliases
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
     * @param Condition                       $condition
     * @param Select                          $select
     * @param MemberMappingWithTableAliases[] $memberMappings
     *
     * @return Expr
     * @throws InvalidArgumentException
     */
    private function mapCondition(Condition $condition, Select $select, array $memberMappings) : Expr
    {
        /** @var MemberMappingWithTableAliases[] $memberMappings */

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
     * @param MemberOrdering                  $ordering
     * @param Select                          $select
     * @param MemberMappingWithTableAliases[] $memberMappings
     *
     * @return void
     */
    private function mapOrdering(MemberOrdering $ordering, Select $select, array $memberMappings)
    {
        $mapping = $memberMappings[$ordering->getNestedMember()->asString()];

        $mapping->getMapping()
            ->addOrderByToSelect($select, $mapping->getLastTableAlias(), $ordering->isAsc());
    }

    /**
     * @param MemberCondition                 $condition
     * @param Select                          $select
     * @param MemberMappingWithTableAliases[] $memberMappings
     *
     * @return Expr
     */
    private function mapMemberCondition(MemberCondition $condition, Select $select, array $memberMappings) : Expr
    {
        $mapping = $memberMappings[$condition->getNestedMember()->asString()];

        return $mapping->getMapping()
            ->getWhereConditionExpr($select, $mapping->getLastTableAlias(), $condition->getOperator(), $condition->getValue());
    }
}