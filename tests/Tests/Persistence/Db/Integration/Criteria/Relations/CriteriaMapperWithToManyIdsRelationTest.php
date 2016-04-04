<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Criteria;

use Dms\Core\Model\Criteria\SpecificationDefinition;
use Dms\Core\Model\EntityIdCollection;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Criteria\CriteriaMapper;
use Dms\Core\Persistence\Db\Criteria\MemberExpressionMappingException;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyIdRelation\ChildEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyIdRelation\ParentEntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyIdRelation\ParentEntity;

/**
 * @see    Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyIdRelation\ParentEntityMapper
 * @see    Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyIdRelation\ChildEntityMapper
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CriteriaMapperWithToManyIdsRelationTest extends CriteriaMapperTestBase
{
    /**
     * @var Table[]
     */
    protected $tables;

    protected function buildMapper()
    {
        return new CriteriaMapper(
                ParentEntityMapper::orm()->getEntityMapper(ParentEntity::class),
                $this->getMockForAbstractClass(IConnection::class)
        );
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

    public function testConditionWithToManyIdCollectionThrows()
    {
        $this->setExpectedException(MemberExpressionMappingException::class);

        $this->mapper->mapCriteriaToSelect(
                $this->mapper->newCriteria()
                        ->where('childIds', '=', new EntityIdCollection())
        );
    }

    public function testOrderByWithToManyIdCollectionThrows()
    {
        $this->setExpectedException(MemberExpressionMappingException::class);

        $this->mapper->mapCriteriaToSelect(
                $this->mapper->newCriteria()
                        ->orderByAsc('childIds')
        );
    }

    public function testConditionWithToManyCollectionThrows()
    {
        $this->setExpectedException(MemberExpressionMappingException::class);

        $this->mapper->mapCriteriaToSelect(
                $this->mapper->newCriteria()
                        ->where('loadAll(childIds)', '=', ChildEntity::collection())
        );
    }

    public function testOrderByWithToManyCollectionThrows()
    {
        $this->setExpectedException(MemberExpressionMappingException::class);

        $this->mapper->mapCriteriaToSelect(
                $this->mapper->newCriteria()
                        ->orderByAsc('loadAll(childIds)')
        );
    }

    public function testCountAggregateInCondition()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('childIds.count()', '>', 2);

        $this->assertMappedSelect($criteria,
                $this->select()
                        ->addRawColumn('id')
                        ->where(Expr::greaterThan(
                                Expr::subSelect(
                                        $this->subSelectChildEntities()
                                                ->addColumn('__single_val', Expr::count())
                                ),
                                Expr::param(null, 2)
                        ))
        );
    }

    public function testSumAggregateInCondition()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('loadAll(childIds).sum(val)', '<', 10);

        $this->assertMappedSelect($criteria,
                $this->select()
                        ->addRawColumn('id')
                        ->where(Expr::lessThan(
                                Expr::subSelect(
                                        $this->subSelectChildEntities()
                                                ->addColumn('__single_val', Expr::sum($this->tableColumn('child_entities', 'val')))
                                ),
                                Expr::param(null, 10)
                        ))
        );
    }

    public function testAverageAggregateInCondition()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('loadAll(childIds).average(val)', '<', 10);

        $this->assertMappedSelect($criteria,
                $this->select()
                        ->addRawColumn('id')
                        ->where(Expr::lessThan(
                                Expr::subSelect(
                                        $this->subSelectChildEntities()
                                                ->addColumn('__single_val', Expr::avg($this->tableColumn('child_entities', 'val')))
                                ),
                                Expr::param(null, 10)
                        ))
        );
    }

    public function testMaxAggregateInCondition()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('loadAll(childIds).max(val)', '<', 10);

        $this->assertMappedSelect($criteria,
                $this->select()
                        ->addRawColumn('id')
                        ->where(Expr::lessThan(
                                Expr::subSelect(
                                        $this->subSelectChildEntities()
                                                ->addColumn('__single_val', Expr::max($this->tableColumn('child_entities', 'val')))
                                ),
                                Expr::param(null, 10)
                        ))
        );
    }

    public function testMinAggregateInCondition()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('loadAll(childIds).min(val)', '<', 10);

        $this->assertMappedSelect($criteria,
                $this->select()
                        ->addRawColumn('id')
                        ->where(Expr::lessThan(
                                Expr::subSelect(
                                        $this->subSelectChildEntities()
                                                ->addColumn('__single_val', Expr::min($this->tableColumn('child_entities', 'val')))
                                ),
                                Expr::param(null, 10)
                        ))
        );
    }

    public function testOrderByAggregate()
    {
        $criteria = $this->mapper->newCriteria()
                ->orderByAsc('loadAll(childIds).average(val)');

        $this->assertMappedSelect($criteria,
                $this->select()
                        ->addRawColumn('id')
                        ->orderByAsc(Expr::subSelect(
                                $this->subSelectChildEntities()
                                        ->addColumn('__single_val', Expr::avg($this->tableColumn('child_entities', 'val')))
                        ))
        );
    }

    public function testWhereHasAll()
    {
        $criteria = $this->mapper->newCriteria()
            ->whereHasAll('loadAll(childIds)', ChildEntity::specification(function (SpecificationDefinition $match) {
                $match->where('val', '>', 5);
            }));

        $this->assertMappedSelect($criteria,
            $this->select()
                ->addRawColumn('id')
                ->where(Expr::subSelect(
                    $this->subSelectChildEntities()
                        ->addColumn('__single_val', Expr::equal(Expr::count(), Expr::param(null, 0)))
                        ->where(Expr::not(Expr::greaterThan($this->tableColumn('child_entities', 'val'), Expr::param(null, 5))))
                ))
        );
    }

    public function testWhereHasAny()
    {
        $criteria = $this->mapper->newCriteria()
            ->whereHasAny('loadAll(childIds)', ChildEntity::specification(function (SpecificationDefinition $match) {
                $match->where('val', '=', 2);
            }));

        $this->assertMappedSelect($criteria,
            $this->select()
                ->addRawColumn('id')
                ->where(Expr::subSelect(
                    $this->subSelectChildEntities()
                        ->addColumn('__single_val', Expr::greaterThan(Expr::count(), Expr::param(null, 0)))
                        ->where(Expr::equal($this->tableColumn('child_entities', 'val'), Expr::param(null, 2)))
                ))
        );
    }
}