<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Locking;

use Dms\Core\Persistence\Db\Mapping\EntityOutOfSyncException;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Mapping\Locking\DateTimeVersionLockingStrategy;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\MultiVersion\MultiVersionedEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\MultiVersion\MultiVersionedEntityMapper;
use Dms\Core\Util\IClock;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MultiVersionTest extends DbIntegrationTest
{
    /**
     * @var \DateTimeImmutable
     */
    private $mockedCurrentTime;

    /**
     * @return IOrm
     */
    protected function loadOrm()
    {
        return MultiVersionedEntityMapper::orm();
    }

    public function setUp()
    {
        parent::setUp();

        /** @var IClock|\PHPUnit_Framework_MockObject_MockObject $clock */
        $clock = $this->getMockForAbstractClass(IClock::class);
        $clock
                ->method('utcNow')
                ->willReturnCallback(function () {
                    return $this->mockedCurrentTime;
                });

        foreach ($this->mapper->getDefinition()->getLockingStrategies() as $lockingStrategy) {
            if ($lockingStrategy instanceof DateTimeVersionLockingStrategy) {
                $lockingStrategy->setClock($clock);
            }
        }
    }


    public function testPersistNew()
    {
        $this->mockedCurrentTime = new \DateTimeImmutable('2000-01-01 12:00:00');

        $this->repo->saveAll([
                new MultiVersionedEntity(),
                new MultiVersionedEntity(),
                new MultiVersionedEntity(),
        ]);

        $this->assertDatabaseDataSameAs([
                'data' => [
                        ['id' => 1, 'int_version' => 1, 'date_version' => '2000-01-01 12:00:00'],
                        ['id' => 2, 'int_version' => 1, 'date_version' => '2000-01-01 12:00:00'],
                        ['id' => 3, 'int_version' => 1, 'date_version' => '2000-01-01 12:00:00'],
                ]
        ]);
    }

    public function testLoad()
    {
        $this->setDataInDb([
                'data' => [
                        ['id' => 1, 'int_version' => 1, 'date_version' => '2000-01-01 12:00:00'],
                        ['id' => 2, 'int_version' => 1, 'date_version' => '2001-01-01 12:00:00'],
                        ['id' => 3, 'int_version' => 1, 'date_version' => '2002-01-01 12:00:00'],
                ]
        ]);

        $this->assertEquals([
                new MultiVersionedEntity(1, 1, new \DateTimeImmutable('2000-01-01 12:00:00')),
                new MultiVersionedEntity(2, 1, new \DateTimeImmutable('2001-01-01 12:00:00')),
                new MultiVersionedEntity(3, 1, new \DateTimeImmutable('2002-01-01 12:00:00')),
        ], $this->repo->getAll());
    }

    public function testPersistExistingSetsVersionToNow()
    {
        $this->mockedCurrentTime = new \DateTimeImmutable('2010-01-01 12:00:00');

        $this->setDataInDb([
                'data' => [
                        ['id' => 1, 'int_version' => 1, 'date_version' => '2000-01-01 12:00:00'],
                        ['id' => 2, 'int_version' => 5, 'date_version' => '2001-01-01 12:00:00'],
                        ['id' => 3, 'int_version' => 10, 'date_version' => '2002-01-01 12:00:00'],
                ]
        ]);

        $this->repo->saveAll([
                $entity1 = new MultiVersionedEntity(1, 1, new \DateTimeImmutable('2000-01-01 12:00:00')),
                $entity2 = new MultiVersionedEntity(2, 5, new \DateTimeImmutable('2001-01-01 12:00:00')),
                $entity3 = new MultiVersionedEntity(3, 10, new \DateTimeImmutable('2002-01-01 12:00:00')),
        ]);

        $this->assertDatabaseDataSameAs([
                'data' => [
                        ['id' => 1, 'int_version' => 2, 'date_version' => '2010-01-01 12:00:00'],
                        ['id' => 2, 'int_version' => 6, 'date_version' => '2010-01-01 12:00:00'],
                        ['id' => 3, 'int_version' => 11, 'date_version' => '2010-01-01 12:00:00'],
                ]
        ]);

        $this->assertEquals(2, $entity1->intVersion);
        $this->assertEquals(6, $entity2->intVersion);
        $this->assertEquals(11, $entity3->intVersion);

        $this->assertEquals(new \DateTimeImmutable('2010-01-01 12:00:00'), $entity1->dateVersion);
        $this->assertEquals(new \DateTimeImmutable('2010-01-01 12:00:00'), $entity2->dateVersion);
        $this->assertEquals(new \DateTimeImmutable('2010-01-01 12:00:00'), $entity3->dateVersion);
    }

    public function testMultiplePersists()
    {
        $entity = new MultiVersionedEntity();

        $this->mockedCurrentTime = new \DateTimeImmutable('2010-01-01 12:00:00');
        $this->repo->save($entity);
        $this->mockedCurrentTime = new \DateTimeImmutable('2010-01-01 12:00:01');
        $this->repo->save($entity);
        $this->mockedCurrentTime = new \DateTimeImmutable('2010-01-01 12:00:02');
        $this->repo->save($entity);
        $this->mockedCurrentTime = new \DateTimeImmutable('2010-01-01 12:00:03');
        $this->repo->save($entity);
        $this->mockedCurrentTime = new \DateTimeImmutable('2010-01-01 12:00:04');
        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'data' => [
                        ['id' => 1, 'int_version' => 5, 'date_version' => '2010-01-01 12:00:04'],
                ]
        ]);

        $this->assertSame(5, $entity->intVersion);
        $this->assertEquals(new \DateTimeImmutable('2010-01-01 12:00:04'), $entity->dateVersion);
    }

    public function testOptimisticConcurrencyExceptionIsThrownWhenVersionOutOfSync()
    {
        $this->setDataInDb([
                'data' => [
                        ['id' => 1, 'int_version' => 1, 'date_version' => '2000-01-01 12:00:00'],
                ]
        ]);

        // Test different date_version
        $entity = new MultiVersionedEntity(1, 1, new \DateTimeImmutable('2000-01-01 12:00:01'));

        /** @var EntityOutOfSyncException $exception */
        $exception = $this->assertThrows(function () use ($entity) {
            $this->repo->save($entity);
        }, EntityOutOfSyncException::class);

        $this->assertSame($entity, $exception->getEntityBeingPersisted());
        $this->assertSame(true, $exception->hasCurrentEntityInDb());
        $this->assertEquals(new MultiVersionedEntity(1, 1, new \DateTimeImmutable('2000-01-01 12:00:00')), $exception->getCurrentEntityInDb());

        // Test different int_version
        $this->assertThrows(function () {
            $entity = new MultiVersionedEntity(1, 4, new \DateTimeImmutable('2000-01-01 12:00:00'));
            $this->repo->save($entity);
        }, EntityOutOfSyncException::class);

        // Test different date_version and int_version
        $this->assertThrows(function () {
            $entity = new MultiVersionedEntity(1, 4, new \DateTimeImmutable('2010-01-01 12:00:00'));
            $this->repo->save($entity);
        }, EntityOutOfSyncException::class);
    }

    public function testOptimisticConcurrencyExceptionIsThrownWhenNoMatchingRow()
    {
        $this->setDataInDb([
                'data' => [

                ]
        ]);

        $entity = new MultiVersionedEntity(1, 1, new \DateTimeImmutable('2000-01-01 12:00:00'));

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
        $this->setDataInDb([
                'data' => [
                        ['id' => 1, 'int_version' => 2, 'date_version' => '2010-01-01 12:00:00'],
                        ['id' => 2, 'int_version' => 6, 'date_version' => '2010-01-01 12:00:00'],
                        ['id' => 3, 'int_version' => 11, 'date_version' => '2010-01-01 12:00:00'],
                ]
        ]);

        $this->repo->removeAll([
                new MultiVersionedEntity(1, 1, new \DateTimeImmutable('2000-01-01 12:00:00')),
                new MultiVersionedEntity(2, 5, new \DateTimeImmutable('2001-01-01 12:00:00')),
                new MultiVersionedEntity(3, 2, new \DateTimeImmutable('2002-01-01 12:00:00')),
        ]);

        $this->assertDatabaseDataSameAs([
                'data' => [

                ]
        ]);
    }
}