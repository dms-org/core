<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\ToMany;

use Dms\Core\Model\EntityCollection;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Query\Upsert;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\ParentEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToManyRelation\ChildEntity;
use Dms\Core\Tests\Persistence\Db\Mock\MockDatabase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ToManyRelationTestBase extends DbIntegrationTest
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

    protected function buildTestEntity(array $childrenValues)
    {
        $entity        = new ParentEntity();
        
        foreach ($childrenValues as $value) {
            $entity->children[] = new ChildEntity(null, $value);
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
                                'fk_child_entities_parent_id_parent_entities',
                                ['parent_id'],
                                'parent_entities',
                                ['id'],
                                ForeignKeyMode::CASCADE,
                                $this->deleteForeignKeyMode()
                        ),
                ],
                array_values($this->childEntities->getForeignKeys())
        );
    }

    public function testPersistNoChildren()
    {
        $entity = $this->buildTestEntity([]);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 1]
                ],
                'child_entities'    => []
        ]);

        $this->assertExecutedQueryTypes([
                'Insert parent entities' => Upsert::class,
        ]);
    }

    public function testPersistWithChildren()
    {
        $entity = $this->buildTestEntity([1, 2, 3, 4]);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 1]
                ],
                'child_entities'    => [
                        ['id' => 1, 'parent_id' => 1, 'val' => 1],
                        ['id' => 2, 'parent_id' => 1, 'val' => 2],
                        ['id' => 3, 'parent_id' => 1, 'val' => 3],
                        ['id' => 4, 'parent_id' => 1, 'val' => 4],
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
            $entities[] = $this->buildTestEntity([10, 20, 30]);
        }

        $this->repo->saveAll($entities);

        $this->assertDatabaseDataSameAs([
                'parent_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'child_entities'    => [
                        ['id' => 1, 'parent_id' => 1, 'val' => 10],
                        ['id' => 2, 'parent_id' => 1, 'val' => 20],
                        ['id' => 3, 'parent_id' => 1, 'val' => 30],

                        ['id' => 4, 'parent_id' => 2, 'val' => 10],
                        ['id' => 5, 'parent_id' => 2, 'val' => 20],
                        ['id' => 6, 'parent_id' => 2, 'val' => 30],

                        ['id' => 7, 'parent_id' => 3, 'val' => 10],
                        ['id' => 8, 'parent_id' => 3, 'val' => 20],
                        ['id' => 9, 'parent_id' => 3, 'val' => 30],
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Insert parent entities' => Upsert::class,
                'Insert child entities'  => Upsert::class,
        ]);
    }

    public function testLoad()
    {
        $this->setDataInDb([
                'parent_entities' => [
                        ['id' => 1],
                ],
                'child_entities'    => [
                        ['id' => 1, 'parent_id' => 1, 'val' => 123],
                ]
        ]);


        $entity = $this->buildTestEntity([123]);
        $entity->setId(1);
        $entity->children[0]->setId(1);
        $this->assertEquals($entity, $this->repo->get(1));

        $this->assertExecutedQueryTypes([
                'Load parent entities' => Select::class,
                'Load child entities'  => Select::class,
        ]);
    }

    public function testBulkLoad()
    {
        $this->setDataInDb([
                'parent_entities' => [
                        ['id' => 1],
                        ['id' => 2],
                        ['id' => 3],
                ],
                'child_entities'    => [
                        ['id' => 1, 'parent_id' => 1, 'val' => 10],
                        ['id' => 2, 'parent_id' => 1, 'val' => 20],
                        ['id' => 3, 'parent_id' => 1, 'val' => 30],

                        ['id' => 4, 'parent_id' => 2, 'val' => 10],
                        ['id' => 5, 'parent_id' => 2, 'val' => 20],
                        ['id' => 6, 'parent_id' => 2, 'val' => 30],

                        ['id' => 7, 'parent_id' => 3, 'val' => 10],
                        ['id' => 8, 'parent_id' => 3, 'val' => 20],
                        ['id' => 9, 'parent_id' => 3, 'val' => 30],
                ]
        ]);

        $entities = [];

        foreach (range(1, 3) as $i) {
            $entities[] = $entity = $this->buildTestEntity([10, 20, 30]);
            $entity->setId($i);
            $entity->children[0]->setId($i * 3 - 2);
            $entity->children[1]->setId($i * 3 - 1);
            $entity->children[2]->setId($i * 3);
        }

        // Should still only execute two selects
        $this->assertEquals($entities, $this->repo->getAll());

        $this->assertExecutedQueryTypes([
                'Load all parent entities' => Select::class,
                'Load all child entities'  => Select::class,
        ]);
    }
}