<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria\MemberMapping;

use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Persistence\Db\Criteria\MemberExpressionMappingException;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Relation\MemberRelation;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Relation\ToManyMemberRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IToManyRelation;
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
    public function getRelation() : \Dms\Core\Persistence\Db\Mapping\Relation\IToManyRelation
    {
        return $this->relation;
    }

    /**
     * @return MemberRelation
     */
    public function asMemberRelation() : \Dms\Core\Persistence\Db\Mapping\ReadModel\Relation\MemberRelation
    {
        return new ToManyMemberRelation($this);
    }

    /**
     * @inheritDoc
     */
    public function getWhereConditionExpr(Select $select, string $tableAlias, string $operator, $value) : \Dms\Core\Persistence\Db\Query\Expression\Expr
    {
        throw MemberExpressionMappingException::format(
                'Cannot perform condition with operator \'%s\' on collection of related %s',
                $operator, $this->getRelatedObjectType()
        );
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
        throw MemberExpressionMappingException::format('Cannot select a collection of related %s as a column', $this->getRelatedObjectType());
    }

    /**
     * @inheritDoc
     */
    protected function getSingleValueExpressionInSelect(Select $select, string $tableAlias) : \Dms\Core\Persistence\Db\Query\Expression\Expr
    {
        throw NotImplementedException::method(__METHOD__);
    }
}