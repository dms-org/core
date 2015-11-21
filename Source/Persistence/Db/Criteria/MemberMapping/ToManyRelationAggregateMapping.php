<?php

namespace Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IToManyRelation;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\SimpleAggregate;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;

/**
 * The to-many relation count mapping class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToManyRelationAggregateMapping extends RelationMapping
{
    /**
     * @var IToManyRelation
     */
    protected $relation;

    /**
     * @see SimpleAggregate
     *
     * @var string
     */
    protected $aggregateType;

    /**
     * @var MemberMapping
     */
    protected $argumentMemberMapping;

    /**
     * ToManyRelationAggregateMapping constructor.
     *
     * @param IEntityMapper   $rootEntityMapper
     * @param IRelation[]     $nestedRelations
     * @param IToManyRelation $relation
     * @param string          $aggregateType
     * @param MemberMapping   $argumentMemberMapping
     */
    public function __construct(
            IEntityMapper $rootEntityMapper,
            array $nestedRelations,
            IToManyRelation $relation,
            $aggregateType,
            MemberMapping $argumentMemberMapping
    ) {
        parent::__construct($rootEntityMapper, $nestedRelations, $relation);
        $this->aggregateType         = $aggregateType;
        $this->argumentMemberMapping = $argumentMemberMapping;
    }

    /**
     * @inheritDoc
     */
    protected function getSingleValueExpressionInSelect(Select $select, $tableAlias)
    {
        $argument = $this->argumentMemberMapping->getExpressionInSelect($select, $tableAlias);

        return new SimpleAggregate($this->aggregateType, $argument);
    }
}