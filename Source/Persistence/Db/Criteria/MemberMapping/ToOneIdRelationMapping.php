<?php

namespace Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\EntityRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\ISeparateTableRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IToOneRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\RelationIdentityReference;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;

/**
 * The to-one id relation mapping class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToOneIdRelationMapping extends ToOneRelationMapping
{
    /**
     * @var IToOneRelation|ISeparateTableRelation|EntityRelation
     */
    protected $relation;

    /**
     * ToOneIdRelationMapping constructor.
     *
     * @param IEntityMapper                                        $rootEntityMapper
     * @param IRelation[]                                          $nestedRelations
     * @param ISeparateTableRelation|IToOneRelation|EntityRelation $relation
     */
    public function __construct(IEntityMapper $rootEntityMapper, array $nestedRelations, IToOneRelation $relation)
    {
        InvalidArgumentException::verifyInstanceOf(__METHOD__, 'relation', $relation, ISeparateTableRelation::class);
        InvalidArgumentException::verifyInstanceOf(__METHOD__, 'relation', $relation, EntityRelation::class);
        InvalidArgumentException::verify(
                $relation->getReference() instanceof RelationIdentityReference,
                'relation must be an id reference'
        );

        parent::__construct($rootEntityMapper, $nestedRelations, $relation);
    }

    /**
     * @inheritDoc
     */
    protected function getSingleValueExpressionInSelect(Select $select, $tableAlias)
    {
        return Expr::column($tableAlias, $this->relation->getRelatedPrimaryKey());
    }
}