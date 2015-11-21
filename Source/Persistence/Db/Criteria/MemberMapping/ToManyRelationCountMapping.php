<?php

namespace Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IToManyRelation;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;

/**
 * The to-many relation count mapping class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToManyRelationCountMapping extends RelationMapping
{
    /**
     * @var IToManyRelation
     */
    protected $relation;

    /**
     * ToManyRelationCountMapping constructor.
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
    protected function getSingleValueExpressionInSelect(Select $select, $tableAlias)
    {
        return Expr::count();
    }
}