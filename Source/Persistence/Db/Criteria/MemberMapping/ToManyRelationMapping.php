<?php

namespace Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping;

use Iddigital\Cms\Core\Exception\NotImplementedException;
use Iddigital\Cms\Core\Persistence\Db\Criteria\MemberExpressionMappingException;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\Relation\MemberRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\Relation\ToManyMemberRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IToManyRelation;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;

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
     * ToManyRelationMapping constructor.
     *
     * @param IEntityMapper   $rootEntityMapper
     * @param IRelation[]     $relationsToSubSelect
     * @param IToManyRelation $relation
     */
    public function __construct(IEntityMapper $rootEntityMapper, array $relationsToSubSelect, IToManyRelation $relation)
    {
        parent::__construct($rootEntityMapper, $relationsToSubSelect, $relation);
    }

    /**
     * @return IToManyRelation
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * @return MemberRelation
     */
    public function asMemberRelation()
    {
        return new ToManyMemberRelation($this);
    }

    /**
     * @inheritDoc
     */
    public function getWhereConditionExpr(Select $select, $tableAlias, $operator, $value)
    {
        throw MemberExpressionMappingException::format(
                'Cannot perform condition with operator \'%s\' on collection of related %s',
                $operator, $this->getRelatedObjectType()
        );
    }

    /**
     * @inheritDoc
     */
    public function addOrderByToSelect(Select $select, $tableAlias, $isAsc)
    {
        throw MemberExpressionMappingException::format('Cannot order by collection of related %s', $this->getRelatedObjectType());
    }

    /**
     * @inheritDoc
     */
    public function addSelectColumn(Select $select, $tableAlias, $alias)
    {
        throw MemberExpressionMappingException::format('Cannot select a collection of related %s as a column', $this->getRelatedObjectType());
    }

    /**
     * @inheritDoc
     */
    protected function getSingleValueExpressionInSelect(Select $select, $tableAlias)
    {
        throw NotImplementedException::method(__METHOD__);
    }
}