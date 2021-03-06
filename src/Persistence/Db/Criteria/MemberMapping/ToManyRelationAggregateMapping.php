<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria\MemberMapping;

use Dms\Core\Persistence\Db\Mapping\Hierarchy\IObjectMapping;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IToManyRelation;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Expression\SimpleAggregate;
use Dms\Core\Persistence\Db\Query\Select;

/**
 * The to-many relation count mapping class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToManyRelationAggregateMapping extends RelationMapping
{
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
     * @param IEntityMapper    $rootEntityMapper
     * @param IObjectMapping[] $subclassObjectMappings
     * @param IRelation[]      $relationsToSubSelect
     * @param IToManyRelation  $relation
     * @param string           $aggregateType
     * @param MemberMapping    $argumentMemberMapping
     */
    public function __construct(
        IEntityMapper $rootEntityMapper,
        array $subclassObjectMappings,
        array $relationsToSubSelect,
        IToManyRelation $relation,
        string $aggregateType,
        MemberMapping $argumentMemberMapping
    ) {
        parent::__construct($rootEntityMapper, $subclassObjectMappings, $relationsToSubSelect, $relation);
        $this->aggregateType         = $aggregateType;
        $this->argumentMemberMapping = $argumentMemberMapping;
    }

    /**
     * @return IToManyRelation
     */
    public function getRelation() : IToManyRelation
    {
        return parent::getRelation();
    }

    public function getWhereConditionExpr(Select $select, string $tableAlias, string $operator, $value) : Expr
    {
        if ($this->argumentMemberMapping instanceof ToOneEmbeddedObjectMapping && $this->argumentMemberMapping->isSingleColumnObject()) {
            $value = $this->argumentMemberMapping->getColumnDataForObject($value)->getColumn($this->argumentMemberMapping->getSingleColumn()->getName());
        }

        return parent::getWhereConditionExpr($select, $tableAlias, $operator, $value);
    }

    /**
     * @inheritDoc
     */
    protected function getSingleValueExpressionInSelect(Select $select, string $tableAlias) : Expr
    {
        $argument = $this->argumentMemberMapping->getExpressionInSelect($select, $tableAlias);

        return new SimpleAggregate($this->aggregateType, $argument);
    }
}