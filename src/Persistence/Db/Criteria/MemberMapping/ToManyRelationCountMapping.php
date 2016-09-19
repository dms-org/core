<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria\MemberMapping;

use Dms\Core\Persistence\Db\Mapping\Hierarchy\IObjectMapping;
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
     * @param IEntityMapper    $rootEntityMapper
     * @param IRelation[]      $relationsToSubSelect
     * @param IObjectMapping[] $subclassObjectMappings
     * @param IToManyRelation  $relation
     */
    public function __construct(
        IEntityMapper $rootEntityMapper,
        array $subclassObjectMappings,
        array $relationsToSubSelect,
        IToManyRelation $relation
    ) {
        parent::__construct($rootEntityMapper, $subclassObjectMappings, $relationsToSubSelect, $relation);
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
    protected function getSingleValueExpressionInSelect(Select $select, string $tableAlias) : Expr
    {
        return Expr::count();
    }
}