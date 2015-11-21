<?php

namespace Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping;

use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Exception\NotImplementedException;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
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
class ToManyRelationMapping extends RelationMapping
{
    /**
     * @var IToManyRelation
     */
    protected $relation;

    /**
     * ToManyRelationMapping constructor.
     *
     * @param IEntityMapper   $rootEntityMapper
     * @param IRelation[]     $nestedRelations
     * @param IToManyRelation $relation
     */
    public function __construct(IEntityMapper $rootEntityMapper, array $nestedRelations, IToManyRelation $relation)
    {
        parent::__construct($rootEntityMapper, $nestedRelations, $relation);
    }

    /**
     * @inheritDoc
     */
    public function getWhereConditionExpr(Select $select, $tableAlias, $operator, $value)
    {
        throw InvalidOperationException::format(
                'Cannot perform condition with operator \'%s\' on collection of related %s',
                $operator, $this->getRelatedObjectType()
        );
    }

    /**
     * @inheritDoc
     */
    public function addOrderByToSelect(Select $select, $tableAlias, $isAsc)
    {
        throw InvalidOperationException::format('Cannot order by collection of related %s', $this->getRelatedObjectType());
    }

    /**
     * @inheritDoc
     */
    public function addSelectColumn(Select $select, $tableAlias, $alias)
    {
        throw InvalidOperationException::format('Cannot select a collection of related %s as a column', $this->getRelatedObjectType());
    }

    /**
     * @inheritDoc
     */
    protected function getSingleValueExpressionInSelect(Select $select, $tableAlias)
    {
        throw NotImplementedException::method(__METHOD__);
    }
}