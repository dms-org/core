<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria\MemberMapping;

use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Model\ISpecification;
use Dms\Core\Persistence\Db\Criteria\CriteriaMapper;
use Dms\Core\Persistence\Db\Criteria\EntityMapperProxy;
use Dms\Core\Persistence\Db\Criteria\MemberExpressionMappingException;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Relation\MemberRelation;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Relation\ToManyMemberRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\Embedded\EmbeddedCollectionRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IToManyRelation;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;

/**
 * The to-many relation mapping class.
 *
 * Performing operations directly on a to-many relation is not supported.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToManyRelationMapping extends RelationMapping implements IFinalRelationMemberMapping
{
    /**
     * @var IToManyRelation
     */
    protected $relation;

    /**
     * @var IRelation[]
     */
    protected $relationsToSubselectForWhereExpr;

    /**
     * ToManyRelationMapping constructor.
     *
     * @param IEntityMapper   $rootEntityMapper
     * @param IRelation[]     $relationsToSubSelect
     * @param IToManyRelation $relation
     */
    public function __construct(IEntityMapper $rootEntityMapper, array $relationsToSubSelect, IToManyRelation $relation)
    {
        parent::__construct($rootEntityMapper, $relationsToSubSelect, $relation);

        $this->relationsToSubselectForWhereExpr = $this->relationsToSubSelect;
    }

    /**
     * @return IToManyRelation
     */
    public function getRelation() : IToManyRelation
    {
        return $this->relation;
    }

    /**
     * @return MemberRelation
     */
    public function asMemberRelation() : MemberRelation
    {
        return new ToManyMemberRelation($this);
    }

    /**
     * @inheritDoc
     */
    public function withoutRelationsToSubSelect(int $relationsToRemove)
    {
        $clone                                   = clone $this;
        $clone->relationsToSubselectForWhereExpr = array_slice($clone->relationsToSubselectForWhereExpr, $relationsToRemove);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getWhereConditionExpr(Select $select, string $tableAlias, string $operator, $specification) : Expr
    {
        try {
            $originalRelations          = $this->relationsToSubSelect;
            $this->relationsToSubSelect = $this->relationsToSubselectForWhereExpr;

            if ($operator === ConditionOperator::ALL_SATISFIES || $operator === ConditionOperator::ANY_SATISFIES) {
                /** @var ISpecification|null $specification */
                if ($specification === null) {
                    return Expr::false();
                }

                if ($this->relation instanceof EmbeddedCollectionRelation) {
                    $relatedEntityMapper = new EntityMapperProxy($this->relation->getMapper());
                } else {
                    $relatedEntityMapper = $this->relation->getMapper();
                }

                $relatedCriteriaMapper = new CriteriaMapper($relatedEntityMapper);

                if ($operator === ConditionOperator::ALL_SATISFIES) {
                    return $this->loadExpressionWithNecessarySubselects(
                        $select,
                        $tableAlias,
                        function (Select $subSelect, string $tableAlias) use ($relatedCriteriaMapper, $specification) {
                            $relatedCriteriaMapper->mapCriteriaToExistingSelect($specification->not()->asCriteria(), $subSelect, $tableAlias);

                            return Expr::equal(Expr::count(), Expr::param(null, 0));
                        }
                    );
                } else {
                    return $this->loadExpressionWithNecessarySubselects(
                        $select,
                        $tableAlias,
                        function (Select $subSelect, string $tableAlias) use ($relatedCriteriaMapper, $specification) {
                            $relatedCriteriaMapper->mapCriteriaToExistingSelect($specification->asCriteria(), $subSelect, $tableAlias);

                            return Expr::greaterThan(Expr::count(), Expr::param(null, 0));
                        }
                    );
                }
            }

            throw MemberExpressionMappingException::format(
                'Cannot perform condition with operator \'%s\' on collection of related %s',
                $operator, $this->getRelatedObjectType()
            );
        } finally {
            $this->relationsToSubSelect = $originalRelations;
        }
    }

    /**
     * @inheritDoc
     */
    public function addOrderByToSelect(Select $select, string $tableAlias, bool $isAsc)
    {
        throw MemberExpressionMappingException::format('Cannot order by collection of related %s', $this->getRelatedObjectType());
    }

    /**
     * @inheritDoc
     */
    public function addSelectColumn(Select $select, string $tableAlias, string $alias)
    {
        throw MemberExpressionMappingException::format('Cannot select a collection of related %s as a column',
            $this->getRelatedObjectType());
    }

    /**
     * @inheritDoc
     */
    protected function getSingleValueExpressionInSelect(Select $select, string $tableAlias) : Expr
    {
        throw NotImplementedException::method(__METHOD__);
    }
}