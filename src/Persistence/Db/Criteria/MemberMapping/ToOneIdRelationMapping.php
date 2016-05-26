<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria\MemberMapping;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\Relation\EntityRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\ISeparateTableRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IToOneRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\RelationIdentityReference;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;

/**
 * The to-one id relation mapping class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToOneIdRelationMapping extends ToOneRelationMapping
{
    /**
     * ToOneIdRelationMapping constructor.
     *
     * @param IEntityMapper                                        $rootEntityMapper
     * @param IRelation[]                                          $relationsToSubSelect
     * @param ISeparateTableRelation|IToOneRelation|EntityRelation $relation
     */
    public function __construct(IEntityMapper $rootEntityMapper, array $relationsToSubSelect, IToOneRelation $relation)
    {
        InvalidArgumentException::verifyInstanceOf(__METHOD__, 'relation', $relation, ISeparateTableRelation::class);
        InvalidArgumentException::verifyInstanceOf(__METHOD__, 'relation', $relation, EntityRelation::class);
        InvalidArgumentException::verify(
            $relation->getReference() instanceof RelationIdentityReference,
            'relation must be an id reference'
        );

        parent::__construct($rootEntityMapper, $relationsToSubSelect, $relation);
    }

    /**
     * @inheritDoc
     */
    protected function getSingleValueExpressionInSelect(Select $select, string $tableAlias) : Expr
    {
        /** @var  ISeparateTableRelation|IToOneRelation|EntityRelation $relation */
        $relation = $this->getRelation();

        return Expr::column($tableAlias, $relation->getRelatedPrimaryKey());
    }
}