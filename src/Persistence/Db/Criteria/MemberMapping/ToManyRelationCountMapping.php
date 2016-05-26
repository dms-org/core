<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria\MemberMapping;

use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IToManyRelation;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;

/**
 * The to-many relation count mapping class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToManyRelationCountMapping extends RelationMapping
{
    /**
     * ToManyRelationCountMapping constructor.
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
    public function getRelation() : IToManyRelation
    {
        return parent::getRelation();
    }
    
    /**
     * @inheritDoc
     */
    protected function getSingleValueExpressionInSelect(Select $select, string $tableAlias) : \Dms\Core\Persistence\Db\Query\Expression\Expr
    {
        return Expr::count();
    }
}