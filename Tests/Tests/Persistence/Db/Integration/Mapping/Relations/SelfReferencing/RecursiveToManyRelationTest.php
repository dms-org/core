<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\SelfReferencing;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Persistence\Db\Query\BulkUpdate;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Query\Update;
use Iddigital\Cms\Core\Persistence\Db\Query\Upsert;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\SelfReferencing\ToManyRelation\RecursiveEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\SelfReferencing\ToManyRelation\RecursiveEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Mock\MockDatabase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RecursiveToManyRelationTest extends DbIntegrationTest
{
    /**
     * @var Table
     */
    protected $entities;

    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return RecursiveEntityMapper::orm();
    }

    /**
     * {@inheritDoc}
     */
    protected function buildDatabase(MockDatabase $db, IOrm $orm)
    {
        parent::buildDatabase($db, $orm);
        $this->entities = $db->getTable('recursive_entities')->getStructure();
    }

    protected function buildTestEntity($levels, $parents)
    {
        $entity = new RecursiveEntity();

        if ($levels === 1) {
            return $entity;
        }

        for ($i = 0; $i < $parents; $i++) {
            $entity->parents[] = $this->buildTestEntity($levels - 1, $parents);
        }

        return $entity;
    }

    public function testCreatesForeignKeys()
    {
        $this->assertEquals(
                [
                        new ForeignKey(
                                'fk_recursive_entities_parent_id_recursive_entities',
                                ['parent_id'],
                                'recursive_entities',
                                ['id'],
                                ForeignKeyMode::CASCADE,
                                ForeignKeyMode::SET_NULL
                        ),
                ],
                array_values($this->entities->getForeignKeys())
        );
    }

    public function testPersistSingleLevel()
    {
        $entity = $this->buildTestEntity(1, 1);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'recursive_entities' => [
                        ['id' => 1, 'parent_id' => null],
                ],
        ]);

        $this->assertSame(1, $entity->getId());

        $this->assertExecutedQueryTypes([
                'Insert entity' => Upsert::class,
        ]);
    }

    public function testPersistDeep()
    {
        $entity = $this->buildTestEntity(3, 2);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'recursive_entities' => [
                        ['id' => 1, 'parent_id' => null],
                        ['id' => 2, 'parent_id' => 1],
                        ['id' => 3, 'parent_id' => 1],
                        ['id' => 4, 'parent_id' => 2],
                        ['id' => 5, 'parent_id' => 2],
                        ['id' => 6, 'parent_id' => 3],
                        ['id' => 7, 'parent_id' => 3],
                ],
        ]);

        // Multiple inserts necessary to get the id of the previous level
        $this->assertExecutedQueryTypes([
                'Insert level 1' => Upsert::class,
                'Insert level 2' => Upsert::class,
                'Insert level 3' => Upsert::class,
        ]);
    }

    public function testPersistRecursive()
    {
        $entity            = new RecursiveEntity();
        $entity->parents[] = $entity;

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'recursive_entities' => [
                        ['id' => 1, 'parent_id' => 1],
                ],
        ]);

        $this->assertExecutedQueryTypes([
                'Insert recursive entity'     => Upsert::class,
                'Update recursive entity fks' => BulkUpdate::class,
        ]);
    }

    public function testPersistDeepExisting()
    {
        $this->db->setData([
                'recursive_entities' => [
                        ['id' => 1, 'parent_id' => null],
                ],
        ]);

        $entity = $this->buildTestEntity(3, 2);
        $entity->setId(1);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'recursive_entities' => [
                        ['id' => 1, 'parent_id' => null],
                        ['id' => 2, 'parent_id' => 1],
                        ['id' => 3, 'parent_id' => 1],
                        ['id' => 4, 'parent_id' => 2],
                        ['id' => 5, 'parent_id' => 2],
                        ['id' => 6, 'parent_id' => 3],
                        ['id' => 7, 'parent_id' => 3],
                ],
        ]);

        // Multiple inserts necessary to get the id of the previous level
        $this->assertExecutedQueryTypes([
                'Insert level 1'              => Upsert::class,
                'Dissociate previous level 2' => Update::class,
                'Insert level 2'              => Upsert::class,
                'Insert level 3'              => Upsert::class,
        ]);
    }

    public function testLoad()
    {
        $this->db->setData([
                'recursive_entities' => [
                        ['id' => 1, 'parent_id' => null],
                        ['id' => 2, 'parent_id' => 1],
                        ['id' => 3, 'parent_id' => 1],
                        ['id' => 4, 'parent_id' => 1],
                        ['id' => 5, 'parent_id' => 1],
                        ['id' => 6, 'parent_id' => 2],
                        ['id' => 7, 'parent_id' => 2],
                        ['id' => 8, 'parent_id' => 3],
                ],
        ]);

        $entity = new RecursiveEntity(1, [
                new RecursiveEntity(2, [
                        new RecursiveEntity(6),
                        new RecursiveEntity(7),
                ]),
                new RecursiveEntity(3, [
                        new RecursiveEntity(8)
                ]),
                new RecursiveEntity(4),
                new RecursiveEntity(5),
        ]);

        $this->assertEquals($entity, $this->repo->get(1));

        // Multiple inserts necessary to get the id of the previous level
        $this->assertExecutedQueryTypes([
                'Select level 1' => Select::class,
                'Select level 2' => Select::class,
                'Select level 3' => Select::class,
                'Select level 4' => Select::class,
        ]);
    }

    public function testLoadRecursive()
    {
        $this->db->setData([
                'recursive_entities' => [
                        ['id' => 1, 'parent_id' => 2],
                        ['id' => 2, 'parent_id' => 3],
                        ['id' => 3, 'parent_id' => 1],
                        ['id' => 4, 'parent_id' => 1],
                ],
        ]);

        // Have to do manual assertions on structure because
        // recursive dependency causes infinite recursion in comparators :(
        // Here is the expected structure.
        $entity                                    = new RecursiveEntity(1, [
                new RecursiveEntity(3, [
                        new RecursiveEntity(2)
                ]),
                new RecursiveEntity(4),
        ]);
        $entity->parents[0]->parents[0]->parents[] = $entity;

        /** @var RecursiveEntity $actual */
        $actual = $this->repo->get(1);
        $this->assertSame(1, $actual->getId());
        $this->assertSame(3, $actual->parents[0]->getId());
        $this->assertSame(2, $actual->parents[0]->parents[0]->getId());
        $this->assertSame($actual, $actual->parents[0]->parents[0]->parents[0]);
        $this->assertSame(4, $actual->parents[1]->getId());
        $this->assertSame([], $actual->parents[1]->parents->getAll());

        $this->assertExecutedQueryTypes([
                'Select recursive entity level 1'                                 => Select::class,
                'Select recursive entity level 2'                                 => Select::class,
                'Select recursive entity level 3'                                 => Select::class,
                'Select parent entity (then find already loaded in identity map)' => Select::class,
        ]);
    }

    public function testRemove()
    {
        $this->db->setData([
                'recursive_entities' => [
                        ['id' => 1, 'parent_id' => null],
                        ['id' => 2, 'parent_id' => 1],
                        ['id' => 3, 'parent_id' => 1],
                        ['id' => 4, 'parent_id' => 3],
                ],
        ]);

        $this->repo->removeById(1);

        $this->assertDatabaseDataSameAs([
                'recursive_entities' => [
                        ['id' => 2, 'parent_id' => null],
                        ['id' => 3, 'parent_id' => null],
                        ['id' => 4, 'parent_id' => 3],
                ],
        ]);

        // Multiple inserts necessary to get the id of the previous level
        $this->assertExecutedQueryTypes([
                'Remove foreign key' => Update::class,
                'Delete level'       => Delete::class,
        ]);
    }

    public function testDeleteBulk()
    {
        $this->db->setData([
                'recursive_entities' => [
                        ['id' => 1, 'parent_id' => null],
                        ['id' => 2, 'parent_id' => 1],
                        ['id' => 3, 'parent_id' => 2],
                        ['id' => 4, 'parent_id' => 3],
                ],
        ]);

        $this->repo->removeAllById([1, 3]);

        $this->assertDatabaseDataSameAs([
                'recursive_entities' => [
                        ['id' => 2, 'parent_id' => null],
                        ['id' => 4, 'parent_id' => null],
                ],
        ]);

        // Multiple inserts necessary to get the id of the previous level
        $this->assertExecutedQueryTypes([
                'Remove foreign key' => Update::class,
                'Delete level'       => Delete::class,
        ]);
    }

    public function testRemoveRecursive()
    {
        $this->db->setData([
                'recursive_entities' => [
                        ['id' => 1, 'parent_id' => 1],
                ],
        ]);

        $this->repo->removeById(1);

        $this->assertDatabaseDataSameAs([
                'recursive_entities' => [],
        ]);


        $this->assertExecutedQueryTypes([
                'Remove foreign key' => Update::class,
                'Delete entity'      => Delete::class,
        ]);
    }

    public function testRemoveRecursiveMultiLevel()
    {
        $this->db->setData([
                'recursive_entities' => [
                        ['id' => 1, 'parent_id' => 2],
                        ['id' => 2, 'parent_id' => 3],
                        ['id' => 3, 'parent_id' => 4],
                        ['id' => 4, 'parent_id' => 1],
                ],
        ]);

        $this->repo->removeById(1);

        $this->assertDatabaseDataSameAs([
                'recursive_entities' => [
                        ['id' => 2, 'parent_id' => 3],
                        ['id' => 3, 'parent_id' => 4],
                        ['id' => 4, 'parent_id' => null],
                ],
        ]);


        $this->assertExecutedQueryTypes([
                'Remove foreign key' => Update::class,
                'Delete entity'      => Delete::class,
        ]);
    }

    public function testLoadCriteriaWithRecursiveFlatten()
    {
        $this->db->setData([
                'recursive_entities' => [
                        ['id' => 1, 'parent_id' => 2],
                        ['id' => 2, 'parent_id' => 1],
                ],
        ]);

        $this->assertEquals(
                [
                        ['id' => 1, 'parents' => RecursiveEntity::collection($this->repo->getAllById([2]))],
                        ['id' => 2, 'parents' => RecursiveEntity::collection($this->repo->getAllById([1]))],
                ],
                $this->repo->loadMatching(
                        $this->repo->loadCriteria()
                                ->loadAll([
                                        'id',
                                        'parents.flatten(parents).flatten(parents)' => 'parents',
                                ])
                )
        );
    }
}