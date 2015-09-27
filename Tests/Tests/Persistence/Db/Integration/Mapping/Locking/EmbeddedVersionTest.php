<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Locking;

use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityOutOfSyncException;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\EmbeddedVersion\EntityWithEmbeddedVersion;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\EmbeddedVersion\EntityWithEmbeddedVersionMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\EmbeddedVersion\VersionValueObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedVersionTest extends DbIntegrationTest
{
    /**
     * @return IOrm
     */
    protected function loadOrm()
    {
        return EntityWithEmbeddedVersionMapper::orm();
    }

    public function testPersistNew()
    {
        $this->repo->saveAll([
                new EntityWithEmbeddedVersion(),
                new EntityWithEmbeddedVersion(),
                new EntityWithEmbeddedVersion(),
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
                new EntityWithEmbeddedVersion(1, new VersionValueObject(1)),
                new EntityWithEmbeddedVersion(2, new VersionValueObject(10)),
                new EntityWithEmbeddedVersion(3, new VersionValueObject(5)),
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
                $entity1 = new EntityWithEmbeddedVersion(1, new VersionValueObject(1)),
                $entity2 = new EntityWithEmbeddedVersion(2, new VersionValueObject(10)),
                $entity3 = new EntityWithEmbeddedVersion(3, new VersionValueObject(5)),
        ]);


        $this->assertDatabaseDataSameAs([
                'data' => [
                        ['id' => 1, 'version' => 2],
                        ['id' => 2, 'version' => 11],
                        ['id' => 3, 'version' => 6],
                ]
        ]);

        $this->assertSame(2, $entity1->version->number);
        $this->assertSame(11, $entity2->version->number);
        $this->assertSame(6, $entity3->version->number);
    }

    public function testMultiplePersists()
    {
        $entity = new EntityWithEmbeddedVersion();

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

        $this->assertSame(5, $entity->version->number);
    }

    public function testOptimisticConcurrencyExceptionIsThrownWhenVersionOutOfSync()
    {
        $this->db->setData([
                'data' => [
                        ['id' => 1, 'version' => 2],
                ]
        ]);

        $entity = new EntityWithEmbeddedVersion(1, new VersionValueObject(1));

        /** @var EntityOutOfSyncException $exception */
        $exception = $this->assertThrows(function () use ($entity) {
            $this->repo->save($entity);
        }, EntityOutOfSyncException::class);

        $this->assertSame($entity, $exception->getEntityBeingPersisted());
        $this->assertSame(true, $exception->hasCurrentEntityInDb());
        $this->assertEquals(new EntityWithEmbeddedVersion(1, new VersionValueObject(2)), $exception->getCurrentEntityInDb());
    }

    public function testOptimisticConcurrencyExceptionIsThrownWhenNoMatchingRow()
    {
        $this->db->setData([
                'data' => [

                ]
        ]);

        $entity = new EntityWithEmbeddedVersion(1, new VersionValueObject(1));

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
                new EntityWithEmbeddedVersion(1, new VersionValueObject(1)),
                new EntityWithEmbeddedVersion(2, new VersionValueObject(10)),
                new EntityWithEmbeddedVersion(3, new VersionValueObject(5)),
        ]);

        $this->assertDatabaseDataSameAs([
                'data' => [

                ]
        ]);
    }
}