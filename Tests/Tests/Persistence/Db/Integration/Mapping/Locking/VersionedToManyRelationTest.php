<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Locking;

use Dms\Core\Persistence\Db\Mapping\EntityOutOfSyncException;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\VersionedToManyRelation\ChildEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\VersionedToManyRelation\ParentEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\VersionedToManyRelation\ParentEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class VersionedToManyRelationTest extends DbIntegrationTest
{
    /**
     * @return IOrm
     */
    protected function loadOrm()
    {
        return ParentEntityMapper::orm();
    }

    public function testPersistNew()
    {
        $this->repo->saveAll([
                new ParentEntity(null, [
                        new ChildEntity(),
                        new ChildEntity(),
                        new ChildEntity(),
                ]),
        ]);

        $this->assertDatabaseDataSameAs([
                'parents'  => [
                        ['id' => 1],
                ],
                'children' => [
                        ['id' => 1, 'parent_id' => 1, 'version' => 1],
                        ['id' => 2, 'parent_id' => 1, 'version' => 1],
                        ['id' => 3, 'parent_id' => 1, 'version' => 1],
                ],
        ]);
    }

    public function testLoad()
    {
        $this->db->setData([
                'parents'  => [
                        ['id' => 1],
                ],
                'children' => [
                        ['id' => 1, 'parent_id' => 1, 'version' => 1],
                        ['id' => 2, 'parent_id' => 1, 'version' => 1],
                        ['id' => 3, 'parent_id' => 1, 'version' => 1],
                ],
        ]);

        $this->assertEquals([
                new ParentEntity(1, [
                        new ChildEntity(1, 1),
                        new ChildEntity(2, 1),
                        new ChildEntity(3, 1),
                ]),
        ], $this->repo->getAll());
    }

    public function testPersistExistingIncrementsVersion()
    {
        $this->db->setData([
                'parents'  => [
                        ['id' => 1],
                ],
                'children' => [
                        ['id' => 1, 'parent_id' => 1, 'version' => 1],
                        ['id' => 2, 'parent_id' => 1, 'version' => 5],
                        ['id' => 3, 'parent_id' => 1, 'version' => 2],
                ],
        ]);

        $this->repo->saveAll([
                new ParentEntity(1, [
                        $entity1 = new ChildEntity(1, 1),
                        $entity2 = new ChildEntity(2, 5),
                        $entity3 = new ChildEntity(3, 2),
                ]),
        ]);


        $this->assertDatabaseDataSameAs([
                'parents'  => [
                        ['id' => 1],
                ],
                'children' => [
                        ['id' => 1, 'parent_id' => 1, 'version' => 2],
                        ['id' => 2, 'parent_id' => 1, 'version' => 6],
                        ['id' => 3, 'parent_id' => 1, 'version' => 3],
                ]
        ]);

        $this->assertSame(2, $entity1->version);
        $this->assertSame(6, $entity2->version);
        $this->assertSame(3, $entity3->version);
    }

    public function testMultiplePersists()
    {
        $entity = new ParentEntity(null, [
                $entity1 = new ChildEntity(),
                $entity2 = new ChildEntity(),
                $entity3 = new ChildEntity(),
        ]);

        $this->repo->save($entity);
        $this->repo->save($entity);
        $this->repo->save($entity);
        $this->repo->save($entity);
        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'parents'  => [
                        ['id' => 1],
                ],
                'children' => [
                        ['id' => 1, 'parent_id' => 1, 'version' => 5],
                        ['id' => 2, 'parent_id' => 1, 'version' => 5],
                        ['id' => 3, 'parent_id' => 1, 'version' => 5],
                ]
        ]);

        $this->assertSame(5, $entity1->version);
        $this->assertSame(5, $entity2->version);
        $this->assertSame(5, $entity3->version);
    }

    public function testOptimisticConcurrencyExceptionIsThrownWhenVersionOutOfSync()
    {
        $this->db->setData([
                'parents'  => [
                        ['id' => 1],
                ],
                'children' => [
                        ['id' => 1, 'parent_id' => 1, 'version' => 3],
                ]
        ]);

        $entity = new ParentEntity(1, [
                $childEntity = new ChildEntity(1, 2),
        ]);

        /** @var EntityOutOfSyncException $exception */
        $exception = $this->assertThrows(function () use ($entity) {
            $this->repo->save($entity);
        }, EntityOutOfSyncException::class);

        $this->assertSame($childEntity, $exception->getEntityBeingPersisted());
        $this->assertSame(true, $exception->hasCurrentEntityInDb());
        $this->assertEquals(new ChildEntity(1, 3), $exception->getCurrentEntityInDb());
    }

    public function testOptimisticConcurrencyExceptionIsThrownWhenNoMatchingRow()
    {
        $this->db->setData([
                'parents'  => [
                        ['id' => 1],
                ],
                'children' => [

                ]
        ]);

        $entity = new ParentEntity(1, [
                $childEntity = new ChildEntity(1, 2),
        ]);

        /** @var EntityOutOfSyncException $exception */
        $exception = $this->assertThrows(function () use ($entity) {
            $this->repo->save($entity);
        }, EntityOutOfSyncException::class);

        $this->assertSame($childEntity, $exception->getEntityBeingPersisted());
        $this->assertSame(false, $exception->hasCurrentEntityInDb());
        $this->assertSame(null, $exception->getCurrentEntityInDb());
    }

    public function testRemove()
    {
        $this->db->setData([
                'parents'  => [
                        ['id' => 1],
                ],
                'children' => [
                        ['id' => 1, 'parent_id' => 1, 'version' => 5],
                        ['id' => 2, 'parent_id' => 1, 'version' => 4],
                        ['id' => 3, 'parent_id' => 1, 'version' => 3],
                ]
        ]);

        // NOTE: this is an identifying relation so deletes will cascade.
        $this->repo->removeAll([
                new ParentEntity(1, [
                        $entity1 = new ChildEntity(1, 5),
                        $entity2 = new ChildEntity(2, 4),
                        $entity3 = new ChildEntity(3, 3),
                ]),
        ]);

        $this->assertDatabaseDataSameAs([
                'parents'  => [

                ],
                'children' => [

                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Delete parent entity'  => Delete::class,
                'Delete child entities' => Delete::class,
        ]);
    }
}