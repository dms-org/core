<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\ToOneId;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Persistence\Db\Query\BulkUpdate;
use Iddigital\Cms\Core\Persistence\Db\Query\Clause\Join;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Query\Update;
use Iddigital\Cms\Core\Persistence\Db\Query\Upsert;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation\ParentEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation\ParentEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation\SubEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Mock\MockDatabase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToOneIdRelationTest extends DbIntegrationTest
{
    /**
     * @var Table
     */
    protected $parentEntities;

    /**
     * @var Table
     */
    protected $subEntities;

    /**
     * {@inheritDoc}
     */
    protected function buildDatabase(MockDatabase $db, IOrm $orm)
    {
        parent::buildDatabase($db, $orm);
        $this->parentEntities = $db->getTable('parent_entities')->getStructure();
        $this->subEntities    = $db->getTable('sub_entities')->getStructure();
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
                                'fk_sub_entities_parent_id_parent_entities',
                                ['parent_id'],
                                'parent_entities',
                                ['id'],
                                ForeignKeyMode::CASCADE,
                                ForeignKeyMode::SET_NULL
                        ),
                ],
                array_values($this->subEntities->getForeignKeys())
        );
    }

    public function testPersistWithNoChild()
    {
        $entity = new ParentEntity(null, null);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 1]
                ],
                'sub_entities'    => [

                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Insert parent entities' => Upsert::class,
        ]);
    }

    public function testPersist()
    {
        $this->db->setData([
                'sub_entities' => [
                        ['id' => 10, 'parent_id' => null, 'val' => 123]
                ]
        ]);

        $entity = new ParentEntity(null, 10);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 1]
                ],
                'sub_entities'    => [
                        ['id' => 10, 'parent_id' => 1, 'val' => 123]
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Insert parent entities'   => Upsert::class,
                'Update child foreign key' => BulkUpdate::class,
        ]);
    }

    public function testBulkPersist()
    {
        $this->db->setData([
                'sub_entities' => [
                        ['id' => 5, 'parent_id' => null, 'val' => 1],
                        ['id' => 6, 'parent_id' => null, 'val' => 1],
                        ['id' => 7, 'parent_id' => null, 'val' => 1],
                ]
        ]);

        // Should still only produce two queries
        $entities = [];

        foreach (range(5, 7) as $i) {
            $entities[] = new ParentEntity(null, $i);
        }

        $this->repo->saveAll($entities);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'sub_entities'    => [
                        ['id' => 5, 'parent_id' => 1, 'val' => 1],
                        ['id' => 6, 'parent_id' => 2, 'val' => 1],
                        ['id' => 7, 'parent_id' => 3, 'val' => 1],
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Insert parent entities'    => Upsert::class,
                'Update child foreign keys' => BulkUpdate::class,
        ]);
    }

    public function testLoad()
    {
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 1],
                ],
                'sub_entities'    => [
                        ['id' => 100, 'parent_id' => 1, 'val' => 22423],
                ]
        ]);

        $this->assertEquals(new ParentEntity(1, 100), $this->repo->get(1));

        $this->assertExecutedQueryTypes([
                'Load parent entities' => Select::class,
                'Load child ids'  => Select::class,
        ]);

        $this->assertExecutedQueryNumber(2,
                Select::from($this->subEntities)
                    ->addRawColumn('id')
                    ->addRawColumn('parent_id')
                    ->where(Expr::in(
                            Expr::tableColumn($this->subEntities, 'parent_id'),
                            Expr::tuple([Expr::idParam(1)])
                    ))
        );
    }

    public function testBulkLoad()
    {
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'sub_entities'    => [
                        ['id' => 10, 'parent_id' => 1, 'val' => 100],
                        ['id' => 11, 'parent_id' => 2, 'val' => 200],
                        ['id' => 12, 'parent_id' => 3, 'val' => 300],
                ]
        ]);

        // Should still only execute two selects
        $entities = $this->repo->getAll();

        $this->assertEquals([
                new ParentEntity(1, 10),
                new ParentEntity(2, 11),
                new ParentEntity(3, 12),
        ], $entities);

        $this->assertExecutedQueryTypes([
                'Load all parent entities'  => Select::class,
                'Load all child entity ids' => Select::class,
        ]);
    }


    public function testPersistParentWithId()
    {
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 4],
                ],
                'sub_entities'    => [
                        ['id' => 10, 'parent_id' => 4, 'val' => 100],
                        ['id' => 123, 'parent_id' => null, 'val' => 200],
                ]
        ]);

        // If the entity had an id it may
        // have contained previous child entities
        // must perform a update to disassociate previous
        // child foreign keys in case.
        $entity = new ParentEntity(4, 123);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 4]
                ],
                'sub_entities'    => [
                        ['id' => 10, 'parent_id' => null, 'val' => 100],
                        ['id' => 123, 'parent_id' => 4, 'val' => 200],
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Insert parent entities'                                 => Upsert::class,
                'Disassociate previous child entities if there were any' => Update::class,
                'Update new foreign keys'                                  => BulkUpdate::class,
        ]);
    }

    public function testRemove()
    {
        // Removing a parent should set the foreign keys of the children to null
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 1],
                ],
                'sub_entities'    => [
                        ['id' => 10, 'parent_id' => 1, 'val' => 100],
                ]
        ]);

        $this->repo->removeById(1);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [],
                'sub_entities'    => [
                        ['id' => 10, 'parent_id' => null, 'val' => 100],
                ]
        ]);
    }

    public function testRemoveBulk()
    {
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'sub_entities'    => [
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
                'sub_entities'    => [
                        ['id' => 10, 'parent_id' => null, 'val' => 100],
                        ['id' => 11, 'parent_id' => 2, 'val' => 200],
                        ['id' => 12, 'parent_id' => null, 'val' => 300],
                ]
        ]);
    }

    public function testLoadCriteria()
    {
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 1],
                ],
                'sub_entities'    => [
                        ['id' => 10, 'parent_id' => 1, 'val' => 100],
                ]
        ]);

        $this->assertEquals(
                [
                        ['childId' => 10, 'child' => new SubEntity(100, 10), 'child-val' => 100],
                ],
                $this->repo->loadMatching(
                        $this->repo->loadCriteria()
                                ->loadAll([
                                        'childId',
                                        'load(childId)' => 'child',
                                        'load(childId).val' => 'child-val',
                                ])
                ));
    }
}