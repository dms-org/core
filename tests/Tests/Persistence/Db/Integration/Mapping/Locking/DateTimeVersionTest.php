<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Locking;

use Dms\Core\Persistence\Db\Mapping\EntityOutOfSyncException;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Mapping\Locking\DateTimeVersionLockingStrategy;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\DateTimeVersion\DateTimeVersionedEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Locking\Fixtures\DateTimeVersion\DateTimeVersionedEntityMapper;
use Dms\Core\Util\IClock;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateTimeVersionTest extends DbIntegrationTest
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
        return DateTimeVersionedEntityMapper::orm();
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->mockedCurrentTime = new \DateTimeImmutable();

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
                new DateTimeVersionedEntity(),
                new DateTimeVersionedEntity(),
                new DateTimeVersionedEntity(),
        ]);

        $this->assertDatabaseDataSameAs([
                'data' => [
                        ['id' => 1, 'version' => '2000-01-01 12:00:00'],
                        ['id' => 2, 'version' => '2000-01-01 12:00:00'],
                        ['id' => 3, 'version' => '2000-01-01 12:00:00'],
                ]
        ]);
    }

    public function testLoad()
    {
        $this->setDataInDb([
                'data' => [
                        ['id' => 1, 'version' => '2000-01-01 12:00:00'],
                        ['id' => 2, 'version' => '2001-01-01 12:00:00'],
                        ['id' => 3, 'version' => '2002-01-01 12:00:00'],
                ]
        ]);

        $this->assertEquals([
                new DateTimeVersionedEntity(1, new \DateTimeImmutable('2000-01-01 12:00:00')),
                new DateTimeVersionedEntity(2, new \DateTimeImmutable('2001-01-01 12:00:00')),
                new DateTimeVersionedEntity(3, new \DateTimeImmutable('2002-01-01 12:00:00')),
        ], $this->repo->getAll());
    }

    public function testPersistExistingSetsVersionToNow()
    {
        $this->mockedCurrentTime = new \DateTimeImmutable('2010-01-01 12:00:00');

        $this->setDataInDb([
                'data' => [
                        ['id' => 1, 'version' => '2000-01-01 12:00:00'],
                        ['id' => 2, 'version' => '2001-01-01 12:00:00'],
                        ['id' => 3, 'version' => '2002-01-01 12:00:00'],
                ]
        ]);

        $this->repo->saveAll([
                $entity1 = new DateTimeVersionedEntity(1, new \DateTimeImmutable('2000-01-01 12:00:00')),
                $entity2 = new DateTimeVersionedEntity(2, new \DateTimeImmutable('2001-01-01 12:00:00')),
                $entity3 = new DateTimeVersionedEntity(3, new \DateTimeImmutable('2002-01-01 12:00:00')),
        ]);

        $this->assertDatabaseDataSameAs([
                'data' => [
                        ['id' => 1, 'version' => '2010-01-01 12:00:00'],
                        ['id' => 2, 'version' => '2010-01-01 12:00:00'],
                        ['id' => 3, 'version' => '2010-01-01 12:00:00'],
                ]
        ]);

        $this->assertEquals(new \DateTimeImmutable('2010-01-01 12:00:00'), $entity1->version);
        $this->assertEquals(new \DateTimeImmutable('2010-01-01 12:00:00'), $entity2->version);
        $this->assertEquals(new \DateTimeImmutable('2010-01-01 12:00:00'), $entity3->version);
    }

    public function testMultiplePersists()
    {
        $entity = new DateTimeVersionedEntity();

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
                        ['id' => 1, 'version' => '2010-01-01 12:00:04'],
                ]
        ]);

        $this->assertEquals(new \DateTimeImmutable('2010-01-01 12:00:04'), $entity->version);
    }

    public function testOptimisticConcurrencyExceptionIsThrownWhenVersionOutOfSync()
    {
        $this->setDataInDb([
                'data' => [
                        ['id' => 1, 'version' => '2000-01-01 12:00:00'],
                ]
        ]);

        $entity = new DateTimeVersionedEntity(1, new \DateTimeImmutable('2000-01-01 12:00:01'));

        /** @var EntityOutOfSyncException $exception */
        $exception = $this->assertThrows(function () use ($entity) {
            $this->repo->save($entity);
        }, EntityOutOfSyncException::class);

        $this->assertSame($entity, $exception->getEntityBeingPersisted());
        $this->assertSame(true, $exception->hasCurrentEntityInDb());
        $this->assertEquals(new DateTimeVersionedEntity(1, new \DateTimeImmutable('2000-01-01 12:00:00')), $exception->getCurrentEntityInDb());
    }

    public function testOptimisticConcurrencyExceptionIsThrownWhenNoMatchingRow()
    {
        $this->setDataInDb([
                'data' => [

                ]
        ]);

        $entity = new DateTimeVersionedEntity(1, new \DateTimeImmutable('2000-01-01 12:00:00'));

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
                        ['id' => 1, 'version' => '2010-01-01 12:00:00'],
                        ['id' => 2, 'version' => '2010-01-01 12:00:01'],
                        ['id' => 3, 'version' => '2010-01-01 12:00:02'],
                ]
        ]);

        $this->repo->removeAll([
                new DateTimeVersionedEntity(1, new \DateTimeImmutable('2000-01-01 12:00:00')),
                new DateTimeVersionedEntity(2, new \DateTimeImmutable('2001-01-01 12:00:00')),
                new DateTimeVersionedEntity(3, new \DateTimeImmutable('2002-01-01 12:00:00')),
        ]);

        $this->assertDatabaseDataSameAs([
                'data' => [

                ]
        ]);
    }
}