<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\ToOne;

use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Query\Upsert;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\ParentEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\SubEntity;
use Dms\Core\Tests\Persistence\Db\Mock\MockDatabase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ToOneRelationTestBase extends DbIntegrationTest
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
        $this->subEntities    = $db->getTable('sub_entities')->getStructure();
        $this->parentEntities = $db->getTable('parent_entities')->getStructure();
    }

    protected function buildTestEntity($id = null, $subVal = 123, $subId = null)
    {
        $entity        = new ParentEntity($id);
        $entity->child = new SubEntity($subVal);
        if ($subId !== null) {
            $entity->child->setId($subId);
        }

        return $entity;
    }

    /**
     * @return string
     */
    abstract protected function deleteForeignKeyMode();

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
                                $this->deleteForeignKeyMode()
                        ),
                ],
                array_values($this->subEntities->getForeignKeys())
        );
    }

    public function testPersistWithNoChild()
    {
        $entity = new ParentEntity();

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
        $entity = $this->buildTestEntity(null, 123);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 1]
                ],
                'sub_entities'    => [
                        ['id' => 1, 'parent_id' => 1, 'val' => 123]
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Insert parent entities' => Upsert::class,
                'Insert child entities'  => Upsert::class,
        ]);
    }

    public function testBulkPersist()
    {
        // Should still only produce two queries
        $entities = [];

        foreach (range(1, 3) as $i) {
            $entities[] = $this->buildTestEntity(null, 123);
        }

        $this->repo->saveAll($entities);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'sub_entities'    => [
                        ['id' => 1, 'parent_id' => 1, 'val' => 123],
                        ['id' => 2, 'parent_id' => 2, 'val' => 123],
                        ['id' => 3, 'parent_id' => 3, 'val' => 123],
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Insert parent entities' => Upsert::class,
                'Insert child entities'  => Upsert::class,
        ]);
    }

    public function testLoad()
    {
        $this->db->setData([
                'parent_entities' => [
                        ['id' => 1],
                ],
                'sub_entities'    => [
                        ['id' => 1, 'parent_id' => 1, 'val' => 123],
                ]
        ]);

        $this->assertEquals($this->buildTestEntity(1, 123, 1), $this->repo->get(1));

        $this->assertExecutedQueryTypes([
                'Load parent entities' => Select::class,
                'Load child entities'  => Select::class,
        ]);
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
                $this->buildTestEntity($id = 1, 100, $subId = 10),
                $this->buildTestEntity($id = 2, 200, $subId = 11),
                $this->buildTestEntity($id = 3, 300, $subId = 12),
        ], $entities);

        $this->assertExecutedQueryTypes([
                'Load all parent entities' => Select::class,
                'Load all child entities'  => Select::class,
        ]);
    }
}