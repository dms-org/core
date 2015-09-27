<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Locking;

use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityOutOfSyncException;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\IntegerVersion\IntegerVersionedEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\IntegerVersion\IntegerVersionedEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IntegerVersionTest extends DbIntegrationTest
{
    /**
     * @return IOrm
     */
    protected function loadOrm()
    {
        return IntegerVersionedEntityMapper::orm();
    }

    public function testPersistNew()
    {
        $this->repo->saveAll([
                new IntegerVersionedEntity(),
                new IntegerVersionedEntity(),
                new IntegerVersionedEntity(),
        ]);

        $this->assertDatabaseDataSameAs([
                'data' => [
                        ['id' => 1, 'version' => 1],
                        ['id' => 2, 'version' => 1],
                        ['id' => 3, 'version' => 1],
                ]
        ]);
    }

    public function testLoad()
    {
        $this->db->setData([
                'data' => [
                        ['id' => 1, 'version' => 1],
                        ['id' => 2, 'version' => 10],
                        ['id' => 3, 'version' => 5],
                ]
        ]);

        $this->assertEquals([
                new IntegerVersionedEntity(1, 1),
                new IntegerVersionedEntity(2, 10),
                new IntegerVersionedEntity(3, 5),
        ], $this->repo->getAll());
    }

    public function testPersistExistingIncrementsVersion()
    {
        $this->db->setData([
                'data' => [
                        ['id' => 1, 'version' => 1],
                        ['id' => 2, 'version' => 10],
                        ['id' => 3, 'version' => 5],
                ]
        ]);

        $this->repo->saveAll([
                $entity1 = new IntegerVersionedEntity(1, 1),
                $entity2 = new IntegerVersionedEntity(2, 10),
                $entity3 = new IntegerVersionedEntity(3, 5),
        ]);

        $this->assertDatabaseDataSameAs([
                'data' => [
                        ['id' => 1, 'version' => 2],
                        ['id' => 2, 'version' => 11],
                        ['id' => 3, 'version' => 6],
                ]
        ]);

        $this->assertSame(2, $entity1->version);
        $this->assertSame(11, $entity2->version);
        $this->assertSame(6, $entity3->version);
    }

    public function testMultiplePersists()
    {
        $entity = new IntegerVersionedEntity();

        $this->repo->save($entity);
        $this->repo->save($entity);
        $this->repo->save($entity);
        $this->repo->save($entity);
        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'data' => [
                        ['id' => 1, 'version' => 5],
                ]
        ]);

        $this->assertSame(5, $entity->version);
    }

    public function testOptimisticConcurrencyExceptionIsThrownWhenVersionOutOfSync()
    {
        $this->db->setData([
                'data' => [
                        ['id' => 1, 'version' => 2],
                ]
        ]);

        $entity = new IntegerVersionedEntity(1, 1);

        /** @var EntityOutOfSyncException $exception */
        $exception = $this->assertThrows(function () use ($entity) {
            $this->repo->save($entity);
        }, EntityOutOfSyncException::class);

        $this->assertSame($entity, $exception->getEntityBeingPersisted());
        $this->assertSame(true, $exception->hasCurrentEntityInDb());
        $this->assertEquals(new IntegerVersionedEntity(1, 2), $exception->getCurrentEntityInDb());
    }

    public function testOptimisticConcurrencyExceptionIsThrownWhenNoMatchingRow()
    {
        $this->db->setData([
                'data' => [

                ]
        ]);

        $entity = new IntegerVersionedEntity(1, 1);

        /** @var EntityOutOfSyncException $exception */
        $exception = $this->assertThrows(function () use ($entity) {
            $this->repo->save($entity);
        }, EntityOutOfSyncException::class);

        $this->assertSame($entity, $exception->getEntityBeingPersisted());
        $this->assertSame(false, $exception->hasCurrentEntityInDb());
        $this->assertSame(null, $exception->getCurrentEntityInDb());
    }

    public function testRemove()
    {
        $this->db->setData([
                'data' => [
                        ['id' => 1, 'version' => 1],
                        ['id' => 2, 'version' => 10],
                        ['id' => 3, 'version' => 5],
                ]
        ]);

        $this->repo->removeAll([
                new IntegerVersionedEntity(1, 1),
                new IntegerVersionedEntity(2, 10),
                new IntegerVersionedEntity(3, 5),
        ]);

        $this->assertDatabaseDataSameAs([
                'data' => [

                ]
        ]);
    }
}