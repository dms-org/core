<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Criteria;

use Dms\Core\Persistence\Db\Criteria\CriteriaMapper;
use Dms\Core\Persistence\Db\Criteria\MemberExpressionMappingException;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\ChildEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\IdentifyingParentEntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\ParentEntity;

/**
 * @see    Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\IdentifyingParentEntityMapper
 * @see    Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\ChildEntityMapper
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CriteriaMapperWithToManyRelationTest extends CriteriaMapperTestBase
{
    /**
     * @var Table[]
     */
    protected $tables;

    protected function buildMapper()
    {
        return new CriteriaMapper(IdentifyingParentEntityMapper::orm()->getEntityMapper(ParentEntity::class));
    }

    /**
     * @return Select
     */
    private function subSelectChildEntities()
    {
        return Select::from($this->tables['child_entities'])
                ->where(Expr::equal(
                        $this->column('id'),
                        $this->tableColumn('child_entities', 'parent_id')
                ));
    }

    public function testConditionWithToManyCollectionThrows()
    {
        $this->setExpectedException(MemberExpressionMappingException::class);

        $this->mapper->mapCriteriaToSelect(
                $this->mapper->newCriteria()
                        ->where('children', '=', ChildEntity::collection([]))
        );
    }

    public function testOrderByWithToManyCollectionThrows()
    {
        $this->setExpectedException(MemberExpressionMappingException::class);

        $this->mapper->mapCriteriaToSelect(
                $this->mapper->newCriteria()
                        ->orderByAsc('children')
        );
    }

    public function testCountAggregateInCondition()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('children.count()', '>', 2);

        $this->assertMappedSelect($criteria,
                $this->select()
                        ->addRawColumn('id')
                        ->where(Expr::greaterThan(
                                Expr::subSelect(
                                        $this->subSelectChildEntities()
                                                ->addColumn('__single_val', Expr::count())
                                ),
                                Expr::param(Integer::normal(), 2)
                        ))
        );
    }

    public function testSumAggregateInCondition()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('children.sum(val)', '<', 10);

        $this->assertMappedSelect($criteria,
                $this->select()
                        ->addRawColumn('id')
                        ->where(Expr::lessThan(
                                Expr::subSelect(
                                        $this->subSelectChildEntities()
                                                ->addColumn('__single_val', Expr::sum($this->tableColumn('child_entities', 'val')))
                                ),
                                Expr::param($this->tableColumnType('child_entities', 'val'), 10)
                        ))
        );
    }

    public function testAverageAggregateInCondition()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('children.average(val)', '<', 10);

        $this->assertMappedSelect($criteria,
                $this->select()
                        ->addRawColumn('id')
                        ->where(Expr::lessThan(
                                Expr::subSelect(
                                        $this->subSelectChildEntities()
                                                ->addColumn('__single_val', Expr::avg($this->tableColumn('child_entities', 'val')))
                                ),
                                Expr::param(Expr::avg($this->tableColumn('child_entities', 'val'))->getResultingType(), 10)
                        ))
        );
    }

    public function testMaxAggregateInCondition()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('children.max(val)', '<', 10);

        $this->assertMappedSelect($criteria,
                $this->select()
                        ->addRawColumn('id')
                        ->where(Expr::lessThan(
                                Expr::subSelect(
                                        $this->subSelectChildEntities()
                                                ->addColumn('__single_val', Expr::max($this->tableColumn('child_entities', 'val')))
                                ),
                                Expr::param($this->tableColumnType('child_entities', 'val'), 10)
                        ))
        );
    }

    public function testMinAggregateInCondition()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('children.min(val)', '<', 10);

        $this->assertMappedSelect($criteria,
                $this->select()
                        ->addRawColumn('id')
                        ->where(Expr::lessThan(
                                Expr::subSelect(
                                        $this->subSelectChildEntities()
                                                ->addColumn('__single_val', Expr::min($this->tableColumn('child_entities', 'val')))
                                ),
                                Expr::param($this->tableColumnType('child_entities', 'val'), 10)
                        ))
        );
    }

    public function testOrderByAggregate()
    {
        $criteria = $this->mapper->newCriteria()
                ->orderByAsc('children.average(val)');

        $this->assertMappedSelect($criteria,
                $this->select()
                        ->addRawColumn('id')
                        ->orderByAsc(Expr::subSelect(
                                $this->subSelectChildEntities()
                                        ->addColumn('__single_val', Expr::avg($this->tableColumn('child_entities', 'val')))
                        ))
        );
    }
}