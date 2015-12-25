<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\ToManyId;

use Dms\Core\Model\EntityIdCollection;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Query\BulkUpdate;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Query\Upsert;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyIdRelation\ChildEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyIdRelation\ParentEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyIdRelation\ParentEntityMapper;
use Dms\Core\Tests\Persistence\Db\Mock\MockDatabase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToManyIdRelationTest extends DbIntegrationTest
{
    /**
     * @var Table
     */
    protected $parentEntities;

    /**
     * @var Table
     */
    protected $childEntities;

    /**
     * {@inheritDoc}
     */
    protected function buildDatabase(MockDatabase $db, IOrm $orm)
    {
        parent::buildDatabase($db, $orm);
        $this->parentEntities = $db->getTable('parent_entities')->getStructure();
        $this->childEntities  = $db->getTable('child_entities')->getStructure();
    }

    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return ParentEntityMapper::orm();
    }

    public function testCreatesForeignKeys()
    {
        $this->assertEquals(
                [
                        new ForeignKey(
                                'fk_child_entities_parent_id_parent_entities',
                                ['parent_id'],
                                'parent_entities',
                                ['id'],
                                ForeignKeyMode::CASCADE,
                                ForeignKeyMode::CASCADE
                        ),
                ],
                array_values($this->childEntities->getForeignKeys())
        );
    }

    public function testPersistNoChildren()
    {
        $entity = new ParentEntity(null);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 1]
                ],
                'child_entities'  => []
        ]);

        $this->assertExecutedQueryTypes([
                'Insert parent entities' => Upsert::class,
        ]);
    }

    public function testPersistWithChildren()
    {
        $this->setDataInDb([
                'child_entities' => [
                        ['id' => 10, 'parent_id' => null, 'val' => 1],
                        ['id' => 20, 'parent_id' => null, 'val' => 2],
                        ['id' => 30, 'parent_id' => null, 'val' => 3],
                        ['id' => 40, 'parent_id' => null, 'val' => 4],
                ]
        ]);


        $entity = new ParentEntity(null, [10, 20, 30, 40]);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 1]
                ],
                'child_entities'  => [
                        ['id' => 10, 'parent_id' => 1, 'val' => 1],
                        ['id' => 20, 'parent_id' => 1, 'val' => 2],
                        ['id' => 30, 'parent_id' => 1, 'val' => 3],
                        ['id' => 40, 'parent_id' => 1, 'val' => 4],
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Insert parent entities'           => Upsert::class,
                'Update child entity foreign keys' => BulkUpdate::class,
        ]);
    }

    public function testBulkPersist()
    {
        $this->setDataInDb([
                'child_entities' => [
                        ['id' => 1, 'parent_id' => null, 'val' => 10],
                        ['id' => 2, 'parent_id' => null, 'val' => 20],
                        ['id' => 3, 'parent_id' => null, 'val' => 30],
                        //
                        ['id' => 4, 'parent_id' => null, 'val' => 10],
                        ['id' => 5, 'parent_id' => null, 'val' => 20],
                        ['id' => 6, 'parent_id' => null, 'val' => 30],
                        //
                        ['id' => 7, 'parent_id' => null, 'val' => 10],
                        ['id' => 8, 'parent_id' => null, 'val' => 20],
                        ['id' => 9, 'parent_id' => null, 'val' => 30],
                ]
        ]);

        // Should still only produce two queries
        $entities = [
                new ParentEntity(null, [1, 2, 3]),
                new ParentEntity(null, [4, 5, 6]),
                new ParentEntity(null, [7, 8, 9]),
        ];

        $this->repo->saveAll($entities);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'child_entities'  => [
                        ['id' => 1, 'parent_id' => 1, 'val' => 10],
                        ['id' => 2, 'parent_id' => 1, 'val' => 20],
                        ['id' => 3, 'parent_id' => 1, 'val' => 30],
                        //
                        ['id' => 4, 'parent_id' => 2, 'val' => 10],
                        ['id' => 5, 'parent_id' => 2, 'val' => 20],
                        ['id' => 6, 'parent_id' => 2, 'val' => 30],
                        //
                        ['id' => 7, 'parent_id' => 3, 'val' => 10],
                        ['id' => 8, 'parent_id' => 3, 'val' => 20],
                        ['id' => 9, 'parent_id' => 3, 'val' => 30],
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Insert parent entities'           => Upsert::class,
                'Update child entity foreign keys' => BulkUpdate::class,
        ]);
    }

    public function testLoad()
    {
        $this->setDataInDb([
                'parent_entities' => [
                        ['id' => 1],
                ],
                'child_entities'  => [
                        ['id' => 1, 'parent_id' => 1, 'val' => 123],
                ]
        ]);

        $this->assertEquals(new ParentEntity(1, [1]), $this->repo->get(1));

        $this->assertExecutedQueryTypes([
                'Load parent entities'  => Select::class,
                'Load child entity ids' => Select::class,
        ]);

        $this->assertExecutedQueryNumber(2,
                Select::from($this->childEntities)
                        ->addRawColumn('id')
                        ->addRawColumn('parent_id')
                        ->where(Expr::in(
                                Expr::tableColumn($this->childEntities, 'parent_id'),
                                Expr::tuple([Expr::idParam(1)])
                        ))
        );
    }

    public function testBulkLoad()
    {
        $this->setDataInDb([
                'parent_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'child_entities'  => [
                        ['id' => 1, 'parent_id' => 1, 'val' => 10],
                        ['id' => 2, 'parent_id' => 1, 'val' => 20],
                        ['id' => 3, 'parent_id' => 1, 'val' => 30],
                        //
                        ['id' => 4, 'parent_id' => 2, 'val' => 10],
                        ['id' => 5, 'parent_id' => 2, 'val' => 20],
                        ['id' => 6, 'parent_id' => 2, 'val' => 30],
                        //
                        ['id' => 7, 'parent_id' => 3, 'val' => 10],
                        ['id' => 8, 'parent_id' => 3, 'val' => 20],
                        ['id' => 9, 'parent_id' => 3, 'val' => 30],
                ]
        ]);

        $entities = [
                new ParentEntity(1, [1, 2, 3]),
                new ParentEntity(2, [4, 5, 6]),
                new ParentEntity(3, [7, 8, 9]),
        ];

        // Should still only execute two selects
        $this->assertEquals($entities, $this->repo->getAll());

        $this->assertExecutedQueryTypes([
                'Load all parent entities'  => Select::class,
                'Load all child entity ids' => Select::class,
        ]);
    }


    public function testPersistExisting()
    {
        $this->setDataInDb([
                'parent_entities' => [
                        ['id' => 1],
                        ['id' => 4],
                ],
                'child_entities'  => [
                        ['id' => 1, 'parent_id' => 4, 'val' => 1000],
                        ['id' => 2, 'parent_id' => 4, 'val' => 200],
                        ['id' => 3, 'parent_id' => 1, 'val' => 500],
                ]
        ]);

        // If the entity had an id it may
        // have contained previous child entities
        // must perform a delete (identifying) in case.
        $entity = new ParentEntity(4, [2, 3]);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 1],
                        ['id' => 4],
                ],
                'child_entities'  => [
                        ['id' => 2, 'parent_id' => 4, 'val' => 200],
                        ['id' => 3, 'parent_id' => 4, 'val' => 500],
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Insert parent entities'           => Upsert::class,
                'Remove previous child entities'   => Delete::class,
                'Update child entity foreign keys' => BulkUpdate::class,
        ]);
    }

    public function testRemove()
    {
        // Removing a parent should remove all children with identifying relationships
        $this->setDataInDb([
                'parent_entities' => [
                        ['id' => 1],
                ],
                'child_entities'  => [
                        ['id' => 10, 'parent_id' => 1, 'val' => 100],
                        ['id' => 11, 'parent_id' => 1, 'val' => 200],
                        ['id' => 12, 'parent_id' => 1, 'val' => 300],
                ]
        ]);

        $this->repo->removeById(1);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [],
                'child_entities'  => []
        ]);
    }

    public function testRemoveBulk()
    {
        // Removing a parent should remove all children with identifying relationships
        $this->setDataInDb([
                'parent_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'child_entities'  => [
                        ['id' => 10, 'parent_id' => 1, 'val' => 100],
                        ['id' => 11, 'parent_id' => 2, 'val' => 200],
                        ['id' => 12, 'parent_id' => 3, 'val' => 300],
                ]
        ]);

        $this->repo->removeAllById([1, 3]);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 2],
                ],
                'child_entities'  => [
                        ['id' => 11, 'parent_id' => 2, 'val' => 200],
                ]
        ]);
    }

    public function testLoadCriteria()
    {
        $this->setDataInDb([
                'parent_entities' => [
                        ['id' => 1],
                ],
                'child_entities'  => [
                        ['id' => 10, 'parent_id' => 1, 'val' => 100],
                        ['id' => 11, 'parent_id' => 1, 'val' => 200],
                        ['id' => 12, 'parent_id' => 1, 'val' => 300],
                ]
        ]);

        $this->assertEquals(
                [
                        [
                                'parent_id' => 1,
                                'child_ids' => new EntityIdCollection([10, 11, 12])
                        ],
                ],
                $this->repo->loadMatching(
                        $this->repo->loadCriteria()
                                ->loadAll(['id' => 'parent_id', 'childIds' => 'child_ids'])
                ));
    }

    public function testLoadCriteriaWithLoadObjectReference()
    {
        $this->setDataInDb([
                'parent_entities' => [
                        ['id' => 1],
                ],
                'child_entities'  => [
                        ['id' => 10, 'parent_id' => 1, 'val' => 100],
                        ['id' => 11, 'parent_id' => 1, 'val' => 200],
                        ['id' => 12, 'parent_id' => 1, 'val' => 300],
                ]
        ]);

        $this->assertEquals(
                [
                        [
                                'parent_id'    => 1,
                                'children'     => ChildEntity::collection([
                                        new ChildEntity(10, 100),
                                        new ChildEntity(11, 200),
                                        new ChildEntity(12, 300),
                                ]),
                                'count'        => 3,
                                'count-loaded' => 3,
                                'avg-val'      => 200,
                        ],
                ],
                $this->repo->loadMatching(
                        $this->repo->loadCriteria()
                                ->loadAll([
                                        'id'                             => 'parent_id',
                                        'loadAll(childIds)'              => 'children',
                                        'childIds.count()'               => 'count',
                                        'loadAll(childIds).count()'      => 'count-loaded',
                                        'loadAll(childIds).average(val)' => 'avg-val'
                                ])
                ));
    }
}