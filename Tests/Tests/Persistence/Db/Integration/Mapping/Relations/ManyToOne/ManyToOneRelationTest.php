<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\ManyToOne;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Query\Upsert;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ManyToOneRelation\ParentEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ManyToOneRelation\ParentEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ManyToOneRelation\SubEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Mock\MockDatabase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ManyToOneRelationTest extends DbIntegrationTest
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
        $this->assertEquals([
                new ForeignKey(
                        'fk_parent_entities_child_id_sub_entities',
                        ['child_id'],
                        'sub_entities',
                        ['id'],
                        ForeignKeyMode::CASCADE,
                        ForeignKeyMode::SET_NULL
                )
        ], array_values($this->parentEntities->getForeignKeys()));
    }

    public function testPersistWithNoChild()
    {
        $entity = new ParentEntity();

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 1, 'child_id' => null]
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
        $entity = new ParentEntity(null, new SubEntity(null, 123));

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 1, 'child_id' => 1]
                ],
                'sub_entities'    => [
                        ['id' => 1, 'val' => 123]
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Insert child entities'  => Upsert::class,
                'Insert parent entities' => Upsert::class,
        ]);
    }

    public function testPersistWithSharedChild()
    {
        $child    = new SubEntity(null, 100);
        $entities = [
                new ParentEntity(null, $child),
                new ParentEntity(null, $child),
                new ParentEntity(null, $child),
        ];

        $this->repo->saveAll($entities);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 1, 'child_id' => 1],
                        ['id' => 2, 'child_id' => 1],
                        ['id' => 3, 'child_id' => 1],
                ],
                'sub_entities'    => [
                        ['id' => 1, 'val' => 100]
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Insert child entities'  => Upsert::class,
                'Insert parent entities' => Upsert::class,
        ]);
    }

    public function testBulkPersist()
    {
        // Should still only produce two queries
        $entities = [];

        foreach (range(1, 3) as $i) {
            $entities[] = new ParentEntity(null, new SubEntity(null, 123));
        }

        $this->repo->saveAll($entities);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 1, 'child_id' => 1],
                        ['id' => 2, 'child_id' => 2],
                        ['id' => 3, 'child_id' => 3],
                ],
                'sub_entities'    => [
                        ['id' => 1, 'val' => 123],
                        ['id' => 2, 'val' => 123],
                        ['id' => 3, 'val' => 123],
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Insert child entities'  => Upsert::class,
                'Insert parent entities' => Upsert::class,
        ]);
    }

    public function testLoad()
    {
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 1, 'child_id' => 1]
                ],
                'sub_entities'    => [
                        ['id' => 1, 'val' => 123],
                ]
        ]);

        $this->assertEquals(new ParentEntity(1, new SubEntity(1, 123)), $this->repo->get(1));

        $this->assertExecutedQueryTypes([
                'Load parent entities' => Select::class,
                'Load child entities'  => Select::class,
        ]);
    }

    public function testBulkLoad()
    {
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 1, 'child_id' => 10],
                        ['id' => 2, 'child_id' => 11],
                        ['id' => 3, 'child_id' => 11],
                ],
                'sub_entities'    => [
                        ['id' => 10, 'val' => 100],
                        ['id' => 11, 'val' => 200],
                ]
        ]);

        // Should still only execute two selects
        /** @var ParentEntity[] $entities */
        $entities = $this->repo->getAll();

        $this->assertEquals([
                new ParentEntity(1, new SubEntity(10, 100)),
                new ParentEntity(2, new SubEntity(11, 200)),
                new ParentEntity(3, new SubEntity(11, 200)),
        ], $entities);

        $this->assertSame($entities[1]->child, $entities[2]->child);

        $this->assertExecutedQueryTypes([
                'Load all parent entities' => Select::class,
                'Load all child entities'  => Select::class,
        ]);
    }


    public function testPersistExisting()
    {
        // A many-to-one relation should not affect the child
        // entities if a relationship is removed
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 4, 'child_id' => 1]
                ],
                'sub_entities'    => [
                        ['id' => 1, 'val' => 123]
                ]
        ]);

        $entity = new ParentEntity(4, new SubEntity(null, 500));

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 4, 'child_id' => 2]
                ],
                'sub_entities'    => [
                        ['id' => 1, 'val' => 123],
                        ['id' => 2, 'val' => 500],
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Insert parent entities' => Upsert::class,
                'Insert child entities'  => Upsert::class,
        ]);
    }

    public function testRemove()
    {
        // A many-to-one relation should not remove the child if a parent is removed
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 1, 'child_id' => 10],
                ],
                'sub_entities'    => [
                        ['id' => 10, 'val' => 100],
                ]
        ]);

        $this->repo->removeById(1);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [],
                'sub_entities'    => [
                        ['id' => 10, 'val' => 100],
                ]
        ]);
    }

    public function testRemoveBulk()
    {
        // A many-to-one relation should not remove the child if a parent is removed
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 1, 'child_id' => 10],
                        ['id' => 2, 'child_id' => 11],
                        ['id' => 3, 'child_id' => 11],
                ],
                'sub_entities'    => [
                        ['id' => 10, 'val' => 100],
                        ['id' => 11, 'val' => 200],
                        ['id' => 12, 'val' => 300],
                ]
        ]);

        $this->repo->removeAllById([1, 3]);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 2, 'child_id' => 11],
                ],
                'sub_entities'    => [
                        ['id' => 10, 'val' => 100],
                        ['id' => 11, 'val' => 200],
                        ['id' => 12, 'val' => 300],
                ]
        ]);
    }


    public function testLoadPartial()
    {
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 1, 'child_id' => 10],
                ],
                'sub_entities'    => [
                        ['id' => 10, 'val' => 100],
                ]
        ]);

        $this->assertEquals(
                [
                        ['child' => new SubEntity(10, 100), 'child.val' => 100],
                ],
                $this->repo->loadMatching(
                        $this->repo->loadCriteria()
                                ->loadAll(['child', 'child.val'])
                ));
    }
}