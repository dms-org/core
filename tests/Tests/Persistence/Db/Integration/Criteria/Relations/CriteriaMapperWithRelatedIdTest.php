<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Criteria;

use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Criteria\CriteriaMapper;
use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation\ParentEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation\ParentEntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation\SubEntity;

/**
 * @see    Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation\ParentEntityMapper
 * @see    Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation\SubEntityMapper
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CriteriaMapperWithRelatedIdTest extends CriteriaMapperTestBase
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
    private function loadParentWithSubEntityLeftJoined()
    {
        return $this->select()
                ->addRawColumn('id')
                ->join(Join::left($this->tables['sub_entities'], 'sub_entities', [
                        Expr::equal(
                                $this->column('id'),
                                $this->tableColumn('sub_entities', 'parent_id')
                        )
                ]));
    }

    public function testRelatedIdPropertyCondition()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('childId', '=', 10);

        $this->assertMappedSelect($criteria,
                $this->loadParentWithSubEntityLeftJoined()
                        ->where(Expr::equal(
                                $this->tableColumn('sub_entities', 'id'),
                                Expr::param(null, 10)
                        ))
        );
    }

    public function testLoadRelatedIdEqualsComparesViaId()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('load(childId)', '=', new SubEntity(0, 10));

        $this->assertMappedSelect($criteria,
                $this->loadParentWithSubEntityLeftJoined()
                        ->where(Expr::equal(
                                $this->tableColumn('sub_entities', 'id'),
                                Expr::param(null, 10)
                        ))
        );
    }

    public function testLoadRelatedProperty()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('load(childId).val', '>', 50);

        $this->assertMappedSelect($criteria,
                $this->loadParentWithSubEntityLeftJoined()
                        ->where(Expr::greaterThan(
                                $this->tableColumn('sub_entities', 'val'),
                                Expr::param(null, 50)
                        ))
        );
    }

    public function testRelatedIdEqualsNullComparesViaId()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('childId', '=', null);

        $this->assertMappedSelect($criteria,
                $this->loadParentWithSubEntityLeftJoined()
                        ->where(Expr::isNull($this->tableColumn('sub_entities', 'id')))
        );

        $criteria = $this->mapper->newCriteria()
                ->where('childId', '!=', null);

        $this->assertMappedSelect($criteria,
                $this->loadParentWithSubEntityLeftJoined()
                        ->where(Expr::isNotNull($this->tableColumn('sub_entities', 'id')))
        );
    }

    public function testLoadRelatedIdNullComparesViaId()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('load(childId)', '=', null);

        $this->assertMappedSelect($criteria,
                $this->loadParentWithSubEntityLeftJoined()
                        ->where(Expr::isNull($this->tableColumn('sub_entities', 'id')))
        );

        $criteria = $this->mapper->newCriteria()
                ->where('load(childId)', '!=', null);

        $this->assertMappedSelect($criteria,
                $this->loadParentWithSubEntityLeftJoined()
                        ->where(Expr::isNotNull($this->tableColumn('sub_entities', 'id')))
        );
    }

    public function testRelatedIdInArray()
    {
        $criteria = $this->mapper->newCriteria()
                ->whereIn('childId', [1, 2, 3]);

        $this->assertMappedSelect($criteria,
                $this->loadParentWithSubEntityLeftJoined()
                        ->where(Expr::in(
                                $this->tableColumn('sub_entities', 'id'),
                                Expr::tupleParams(null, [1, 2, 3])
                        ))
        );
    }

    public function testRelatedEntityNotInArray()
    {
        $criteria = $this->mapper->newCriteria()
                ->whereNotIn('childId', [1, 2]);

        $this->assertMappedSelect($criteria,
                $this->loadParentWithSubEntityLeftJoined()
                        ->where(Expr::notIn(
                                $this->tableColumn('sub_entities', 'id'),
                                Expr::tupleParams(null, [1, 2])
                        ))
        );
    }

    public function testLoadRelatedIdEqualsWithNullId()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('load(childId)', '=', new SubEntity(0, null));

        $this->assertMappedSelect($criteria,
                $this->loadParentWithSubEntityLeftJoined()
                        ->where(Expr::false())
        );

        $criteria = $this->mapper->newCriteria()
                ->where('load(childId)', '!=', new SubEntity(0, null));

        $this->assertMappedSelect($criteria,
                $this->loadParentWithSubEntityLeftJoined()
                        ->where(Expr::true())
        );
    }

    public function testOrderByRelatedEntityProperty()
    {
        $criteria = $this->mapper->newCriteria()
                ->orderByAsc('childId')
                ->orderByDesc('load(childId).id')
                ->orderByDesc('load(childId).val');

        $this->assertMappedSelect($criteria,
                $this->loadParentWithSubEntityLeftJoined()
                        ->orderByAsc($this->tableColumn('sub_entities', 'id'))
                        ->orderByDesc($this->tableColumn('sub_entities', 'id'))
                        ->orderByDesc($this->tableColumn('sub_entities', 'val'))
        );
    }
}