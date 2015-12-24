<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Criteria;

use Dms\Core\Persistence\Db\Criteria\CriteriaMapper;
use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\IdentifyingParentEntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\ParentEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\SubEntity;

/**
 * @see    Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\IdentifyingParentEntityMapper
 * @see    Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\SubEntityMapper
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CriteriaMapperWithRelatedEntityTest extends CriteriaMapperTestBase
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

    public function testRelatedEntityPropertyCondition()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('child.val', '>=', 55);

        $this->assertMappedSelect($criteria,
                $this->loadParentWithSubEntityLeftJoined()
                        ->where(Expr::greaterThanOrEqual(
                                $this->tableColumn('sub_entities', 'val'),
                                Expr::param($this->tableColumnType('sub_entities', 'val'), 55))
                        )
        );
    }

    public function testRelatedEntityEqualsComparesViaId()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('child', '=', new SubEntity(0, 10));

        $this->assertMappedSelect($criteria,
                $this->loadParentWithSubEntityLeftJoined()
                        ->where(Expr::equal(
                                $this->tableColumn('sub_entities', 'id'),
                                Expr::param($this->tableColumnType('sub_entities', 'id'), 10)
                        ))
        );
    }

    public function testRelatedEntityEqualsNullComparesViaId()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('child', '=', null);

        $this->assertMappedSelect($criteria,
                $this->loadParentWithSubEntityLeftJoined()
                        ->where(Expr::isNull($this->tableColumn('sub_entities', 'id')))
        );

        $criteria = $this->mapper->newCriteria()
                ->where('child', '!=', null);

        $this->assertMappedSelect($criteria,
                $this->loadParentWithSubEntityLeftJoined()
                        ->where(Expr::isNotNull($this->tableColumn('sub_entities', 'id')))
        );
    }

    public function testRelatedEntityInArray()
    {
        $criteria = $this->mapper->newCriteria()
                ->whereIn('child', [new SubEntity(0, 1), new SubEntity(0, 2), new SubEntity(0, 3)]);

        $this->assertMappedSelect($criteria,
                $this->loadParentWithSubEntityLeftJoined()
                        ->where(Expr::compoundOr([
                                Expr::equal(
                                        $this->tableColumn('sub_entities', 'id'),
                                        Expr::param($this->tableColumnType('sub_entities', 'id'), 1)
                                ),
                                Expr::equal(
                                        $this->tableColumn('sub_entities', 'id'),
                                        Expr::param($this->tableColumnType('sub_entities', 'id'), 2)
                                ),
                                Expr::equal(
                                        $this->tableColumn('sub_entities', 'id'),
                                        Expr::param($this->tableColumnType('sub_entities', 'id'), 3)
                                ),
                        ]))
        );
    }

    public function testRelatedEntityNotInArray()
    {
        $criteria = $this->mapper->newCriteria()
                ->whereNotIn('child', [new SubEntity(0, 1), new SubEntity(0, 2)]);

        $this->assertMappedSelect($criteria,
                $this->loadParentWithSubEntityLeftJoined()
                        ->where(Expr::not(Expr::compoundOr([
                                Expr::equal(
                                        $this->tableColumn('sub_entities', 'id'),
                                        Expr::param($this->tableColumnType('sub_entities', 'id'), 1)
                                ),
                                Expr::equal(
                                        $this->tableColumn('sub_entities', 'id'),
                                        Expr::param($this->tableColumnType('sub_entities', 'id'), 2)
                                ),
                        ])))
        );
    }

    public function testRelatedEntityEqualsWithNullId()
    {
        $criteria = $this->mapper->newCriteria()
                ->where('child', '=', new SubEntity(0, null));

        $this->assertMappedSelect($criteria,
                $this->loadParentWithSubEntityLeftJoined()
                        ->where(Expr::false())
        );

        $criteria = $this->mapper->newCriteria()
                ->where('child', '!=', new SubEntity(0, null));

        $this->assertMappedSelect($criteria,
                $this->loadParentWithSubEntityLeftJoined()
                        ->where(Expr::true())
        );
    }

    public function testOrderByRelatedEntityProperty()
    {
        $criteria = $this->mapper->newCriteria()
                ->orderByAsc('child.id')
                ->orderByDesc('child.val');

        $this->assertMappedSelect($criteria,
                $this->loadParentWithSubEntityLeftJoined()
                        ->orderByAsc($this->tableColumn('sub_entities', 'id'))
                        ->orderByDesc($this->tableColumn('sub_entities', 'val'))
        );
    }
}