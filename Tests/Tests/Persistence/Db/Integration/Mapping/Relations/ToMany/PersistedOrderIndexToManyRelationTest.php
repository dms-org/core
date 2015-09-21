<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\ToMany;

use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Query\Upsert;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\ChildEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\Ordered\ParentWithChildOrderPersistenceColumnEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\ParentEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PersistedOrderIndexToManyRelationTest extends ToManyRelationTestBase
{
    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return ParentWithChildOrderPersistenceColumnEntityMapper::orm();
    }

    /**
     * @inheritDoc
     */
    protected function deleteForeignKeyMode()
    {
        return ForeignKeyMode::SET_NULL;
    }

    public function testPersistNoChildren()
    {

    }

    public function testPersistWithChildren()
    {

    }

    public function testLoad()
    {

    }

    public function testBulkPersist()
    {
        $this->repo->saveAll([
                new ParentEntity(null, [
                        new ChildEntity(null, 10),
                        new ChildEntity(null, 20),
                        new ChildEntity(null, 30),
                ]),
                new ParentEntity(null, [
                        new ChildEntity(null, 999),
                        new ChildEntity(null, 99),
                        new ChildEntity(null, 9),
                ]),
        ]);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                ],
                'child_entities'  => [
                        ['id' => 1, 'parent_id' => 1, 'order_index' => 1, 'val' => 10],
                        ['id' => 2, 'parent_id' => 1, 'order_index' => 2, 'val' => 20],
                        ['id' => 3, 'parent_id' => 1, 'order_index' => 3, 'val' => 30],
                        //
                        ['id' => 4, 'parent_id' => 2, 'order_index' => 1, 'val' => 999],
                        ['id' => 5, 'parent_id' => 2, 'order_index' => 2, 'val' => 99],
                        ['id' => 6, 'parent_id' => 2, 'order_index' => 3, 'val' => 9],
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Insert parent entities' => Upsert::class,
                'Insert child entities'  => Upsert::class,
        ]);
    }

    public function testLoadsInPersistedOrder()
    {
        $this->testBulkPersist();

        $entities = [
                new ParentEntity(1, [
                        new ChildEntity(1, 10),
                        new ChildEntity(2, 20),
                        new ChildEntity(3, 30),
                ]),
                new ParentEntity(2, [
                        new ChildEntity(4, 999),
                        new ChildEntity(5, 99),
                        new ChildEntity(6, 9),
                ]),
        ];

        $this->assertEquals($entities, $this->repo->getAll());
    }


    public function testBulkLoad()
    {
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'child_entities'  => [
                        ['id' => 1, 'parent_id' => 1, 'order_index' => 1, 'val' => -1],
                        ['id' => 2, 'parent_id' => 1, 'order_index' => 2, 'val' => -5],
                        ['id' => 3, 'parent_id' => 1, 'order_index' => 3, 'val' => 30],
                        //
                        ['id' => 4, 'parent_id' => 2, 'order_index' => 3, 'val' => 999],
                        ['id' => 5, 'parent_id' => 2, 'order_index' => 2, 'val' => 99],
                        ['id' => 6, 'parent_id' => 2, 'order_index' => 1, 'val' => 9],
                        //
                        ['id' => 7, 'parent_id' => 3, 'order_index' => 3, 'val' => 1],
                        ['id' => 8, 'parent_id' => 3, 'order_index' => 1, 'val' => 3],
                        ['id' => 9, 'parent_id' => 3, 'order_index' => 2, 'val' => 2],
                ]
        ]);

        $entities = [
                new ParentEntity(1, [
                        new ChildEntity(1, -1),
                        new ChildEntity(2, -5),
                        new ChildEntity(3, 30),
                ]),
                new ParentEntity(2, [
                        new ChildEntity(6, 9),
                        new ChildEntity(5, 99),
                        new ChildEntity(4, 999),
                ]),
                new ParentEntity(3, [
                        new ChildEntity(8, 3),
                        new ChildEntity(9, 2),
                        new ChildEntity(7, 1),
                ]),
        ];

        $this->assertEquals($entities, $this->repo->getAll());

        $this->assertExecutedQueryTypes([
                'Load all parent entities' => Select::class,
                'Load all child entities'  => Select::class,
        ]);

        $this->assertExecutedQueryNumber(2,
                Select::from($this->childEntities)
                        ->addRawColumn('id')
                        ->addRawColumn('parent_id')
                        ->addRawColumn('val')
                        ->where(Expr::in(
                                Expr::tableColumn($this->childEntities, 'parent_id'),
                                Expr::tuple([Expr::idParam(1), Expr::idParam(2), Expr::idParam(3)])
                        ))
                        ->orderByAsc(Expr::tableColumn($this->childEntities, 'order_index'))
        );
    }
}