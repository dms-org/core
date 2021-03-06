<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Model\Criteria\SpecificationDefinition;
use Dms\Core\Model\EntityNotFoundException;
use Dms\Core\Model\IEntity;
use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\DbRepository;
use Dms\Core\Persistence\PersistenceException;
use Dms\Core\Tests\Form\Field\Processor\Validator\Fixtures\TestEntity;
use Dms\Core\Tests\Model\Criteria\Fixtures\MockSpecification;
use Dms\Core\Tests\Persistence\Db\Fixtures\MockEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Id\EmptyEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Id\EmptyEntitySubclass;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Id\EmptyMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DbRepositoryTest extends DbIntegrationTest
{
    protected function loadOrm()
    {
        return CustomOrm::from([EmptyEntity::class => EmptyMapper::class]);
    }

    /**
     * @return array
     */
    protected function makeEntities($amount)
    {
        $entities = [];

        foreach (range(1, $amount) as $i) {
            $entities[] = new EmptyEntity();
        }

        return $entities;
    }

    /**
     * @param $entities
     *
     * @return array
     */
    protected function getIds($entities)
    {
        $ids = [];

        /** @var IEntity $entity */
        foreach ($entities as $entity) {
            $ids[] = $entity->getId();
        }

        return $ids;
    }

    public function testEntityType()
    {
        $this->assertSame(EmptyEntity::class, $this->repo->getEntityType());
        $this->assertSame(EmptyEntity::type(), $this->repo->getElementType());
    }

    public function testGetAllEmpty()
    {
        $this->assertSame([], $this->repo->getAll());
    }

    public function testGetObjectId()
    {
        $entity = new EmptyEntity(5);
        $this->assertSame(5, $this->repo->getObjectId($entity));

        $this->assertThrows(function () {
            $this->repo->getObjectId(new EmptyEntity());
        }, InvalidArgumentException::class);
    }

    public function testCountEmpty()
    {
        $this->assertSame(0, $this->repo->count());
    }

    public function testNotHas()
    {
        $this->assertFalse($this->repo->has(1));
        $this->assertFalse($this->repo->has(2));
        $this->assertFalse($this->repo->has(3));
    }

    public function testSave()
    {
        $entity = new EmptyEntity();

        $this->repo->save($entity);

        $this->assertTrue($entity->hasId());
        $this->assertSame(1, $entity->getId());
        $this->assertTrue($this->repo->has(1));
        $this->assertTrue($this->repo->hasAll([1]));
    }

    public function testSaveAll()
    {
        /** @var IEntity[] $entities */
        $entities = $this->makeEntities(10);

        $this->repo->saveAll($entities);

        $ids = $this->getIds($entities);

        $this->assertSame(range(1, 10), $ids);
        $this->assertTrue($this->repo->hasAll($ids));
    }

    public function testHasAll()
    {
        $this->assertTrue($this->repo->hasAll([]));
    }

    public function testGetInvalidId()
    {
        $entity = new EmptyEntity();
        $this->repo->save($entity);

        $this->assertSame(1, $entity->getId());

        $this->expectException(EntityNotFoundException::class);
        $this->repo->get(2);
    }

    public function testGet()
    {
        $entity = new EmptyEntity();

        $this->repo->save($entity);

        $this->assertSame(1, $entity->getId());
        $this->assertTrue($this->repo->has(1));
        $this->assertEquals($entity, $this->repo->get(1));
        $this->assertNotSame($entity, $this->repo->get(1));

        $this->assertThrows(function () {
            $this->repo->get(100);
        }, EntityNotFoundException::class);
    }

    public function testGetAllByIds()
    {
        $entities = $this->makeEntities(5);

        $this->repo->saveAll($entities);

        $this->assertEquals([$entities[0], $entities[1], $entities[2]], $this->repo->getAllById([1, 2, 3]));

        $this->assertThrows(function () {
            $this->repo->getAllById([1, 2, 3, 100]);
        }, EntityNotFoundException::class);
    }

    public function testGetAllByIdMaintainsArrayKeys()
    {
        $entities = $this->makeEntities(5);

        $this->repo->saveAll($entities);

        $this->repo->get(3);
        $this->assertSame(['a', 'b', 'c'], array_keys($this->repo->getAllById(['a' => 1, 'b' => 2, 'c' => 3])));
        $this->assertEquals(['a' => $entities[0], 'b' => $entities[1], 'c' => $entities[2]], $this->repo->getAllById(['a' => 1, 'b' => 2, 'c' => 3]));
    }

    public function testTryGetInvalidId()
    {
        $this->repo->save(new EmptyEntity());

        $this->assertNull($this->repo->tryGet(2));
    }

    public function testGetAll()
    {
        $entities = $this->makeEntities(5);

        $this->repo->saveAll($entities);

        $this->assertEquals($entities, $this->repo->getAll());
    }

    public function testTryGetAllById()
    {
        $entities = $this->makeEntities(5);

        $this->repo->saveAll($entities);

        $this->assertEquals($entities, $this->repo->tryGetAll($this->getIds($entities)));
    }

    public function testRemove()
    {
        $entity = new EmptyEntity();

        $this->repo->save($entity);
        $this->repo->remove($entity);

        $this->assertFalse($this->repo->has(1));
        $this->assertNull($this->repo->tryGet(1));
        $this->assertSame([], $this->repo->getAll());
    }

    public function testRemoveById()
    {
        $entity = new EmptyEntity();

        $this->repo->save($entity);
        $this->repo->removeById(1);

        $this->assertFalse($this->repo->has(1));
        $this->assertNull($this->repo->tryGet(1));
        $this->assertSame([], $this->repo->getAll());
    }

    public function testRemoveAll()
    {
        $entities = $this->makeEntities(5);

        $this->repo->saveAll($entities);
        $this->repo->removeAll([$entities[2], $entities[3]]);

        $this->assertTrue($this->repo->has(1));
        $this->assertTrue($this->repo->has(2));
        $this->assertFalse($this->repo->has(3));
        $this->assertFalse($this->repo->has(4));
        $this->assertTrue($this->repo->has(5));
        $this->assertEquals([$entities[0], $entities[1], $entities[4]], $this->repo->getAll());
    }

    public function testRemoveAllById()
    {
        $entities = $this->makeEntities(5);

        $this->repo->saveAll($entities);
        $this->repo->removeAllById([3, 4]);

        $this->assertTrue($this->repo->has(1));
        $this->assertTrue($this->repo->has(2));
        $this->assertFalse($this->repo->has(3));
        $this->assertFalse($this->repo->has(4));
        $this->assertTrue($this->repo->has(5));
        $this->assertEquals([$entities[0], $entities[1], $entities[4]], $this->repo->getAll());
    }

    public function testClear()
    {
        $entities = $this->makeEntities(20);

        $this->repo->saveAll($entities);
        $this->repo->clear();

        $this->assertSame([], $this->repo->getAll());
    }

    public function testCount()
    {
        $entities = $this->makeEntities(17);

        $this->repo->saveAll($entities);

        $this->assertSame(17, $this->repo->count());
    }

    public function testInvalidCriteriaType()
    {
        $this->expectException(TypeMismatchException::class);

        $this->repo->matching(MockEntity::criteria());
    }

    public function testInvalidCriteriaTypeForCount()
    {
        $this->expectException(TypeMismatchException::class);

        $this->repo->countMatching(MockEntity::criteria());
    }

    public function testMatchingCriteria()
    {
        $entities = $this->makeEntities(20);

        $this->repo->saveAll($entities);

        $entities = $this->repo->matching(
            EmptyEntity::criteria()
                ->where('id', '<=', 15)
                ->orderByDesc('id')
                ->skip(5)
                ->limit(3)
        );

        $this->assertEquals([
            new EmptyEntity(10),
            new EmptyEntity(9),
            new EmptyEntity(8),
        ], $entities);
    }

    public function testCountMatchingCriteria()
    {
        $count = $this->makeEntities(20);

        $this->repo->saveAll($count);

        $count = $this->repo->countMatching(
            EmptyEntity::criteria()
                ->where('id', '<=', 15)
                ->orderByDesc('id')
                ->skip(5)
        );

        $this->assertSame(10, $count);
    }

    public function testMatchingSpecification()
    {
        $entities = $this->makeEntities(20);

        $this->repo->saveAll($entities);

        $spec = new MockSpecification(EmptyEntity::class, function (SpecificationDefinition $match) {
            $match->where('id', '<=', 5);
        });

        $this->assertEquals([
            new EmptyEntity(1),
            new EmptyEntity(2),
            new EmptyEntity(3),
            new EmptyEntity(4),
            new EmptyEntity(5),
        ], $this->repo->satisfying($spec));
    }

    public function testContains()
    {
        $this->setDataInDb([
            'data' => [
                ['id' => 1],
                ['id' => 2],
            ],
        ]);

        $this->assertSame(true, $this->repo->contains(new EmptyEntity(1)));
        $this->assertSame(true, $this->repo->contains(new EmptyEntity(2)));

        $this->assertSame(false, $this->repo->contains(new EmptyEntity(null)));
        $this->assertSame(false, $this->repo->contains(new EmptyEntity(3)));

        $this->assertThrows(function () {
            $this->repo->contains(new TestEntity(1));
        }, TypeMismatchException::class);
    }

    public function testContainsAll()
    {
        $this->setDataInDb([
            'data' => [
                ['id' => 1],
                ['id' => 2],
            ],
        ]);

        $this->assertSame(true, $this->repo->containsAll([]));
        $this->assertSame(true, $this->repo->containsAll([new EmptyEntity(1)]));
        $this->assertSame(true, $this->repo->containsAll([new EmptyEntity(2)]));
        $this->assertSame(true, $this->repo->containsAll([new EmptyEntity(1), new EmptyEntity(2)]));

        $this->assertSame(false, $this->repo->containsAll([new EmptyEntity(null)]));
        $this->assertSame(false, $this->repo->containsAll([new EmptyEntity(3)]));
        $this->assertSame(false, $this->repo->containsAll([new EmptyEntity(1), new EmptyEntity(2), new EmptyEntity(3)]));

        $this->assertThrows(function () {
            $this->repo->containsAll([new EmptyEntity(1), new EmptyEntity(2), new EmptyEntity(3), new TestEntity(1)]);
        }, TypeMismatchException::class);
    }

    public function testHasAllWithDuplicates()
    {
        $this->setDataInDb([
            'data' => [
                ['id' => 1],
                ['id' => 2],
            ],
        ]);

        $this->assertSame(true, $this->repo->hasAll([1, 2]));
        $this->assertSame(true, $this->repo->hasAll([1, 1, 1, 2]));
        $this->assertSame(true, $this->repo->hasAll([1, 1, 1, 2, 2]));
        $this->assertSame(false, $this->repo->hasAll([1, 1, 1, 2, 2, 3]));
        $this->assertSame(false, $this->repo->hasAll([1, 2, 3]));
    }

    public function testCustomQuery()
    {
        $this->connection->setPreparedStatementResult([
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
        ]);

        $repo = new class($this->connection, $this->mapper) extends DbRepository
        {
            public function loadCustomQuery()
            {
                return $this->loadQuery(
                    'SELECT (columns) FROM (table) AS alias WHERE some_column > :param',
                    ['param' => 5]
                );
            }
        };

        $this->assertEquals([
            new EmptyEntity(1),
            new EmptyEntity(2),
            new EmptyEntity(3),
        ], $repo->loadCustomQuery());

        // @see MockPlatform for identifier escaping
        $this->assertSame([
            ['SELECT !!id!! FROM !!data!! AS alias WHERE some_column > :param', ['param' => 5]],
        ], $this->connection->getQueryLog());
    }

    public function testInvalidCustomQuery()
    {
        $this->connection->setPreparedStatementResult([
            ['invalid_col' => 1],
            ['invalid_col' => 2],
            ['invalid_col' => 3],
        ]);

        $repo = new class($this->connection, $this->mapper) extends DbRepository
        {
            public function loadCustomQuery()
            {
                return $this->loadQuery(
                    'SELECT (columns) FROM (table) AS alias WHERE some_column > :param',
                    ['param' => 5]
                );
            }
        };

        $this->assertThrows(function () use ($repo) {
            $repo->loadCustomQuery();
        }, PersistenceException::class);
    }

    public function testRemoveMatching()
    {
        $entities = $this->makeEntities(10);

        $this->repo->saveAll($entities);

        $this->repo->removeMatching(
            EmptyEntity::criteria()
                ->where('id', '<=', 9)
                ->orderByDesc('id')
                ->skip(5)
                ->limit(3)
        );

        $this->assertEquals([
            new EmptyEntity(1),
            //
            new EmptyEntity(5),
            new EmptyEntity(6),
            new EmptyEntity(7),
            new EmptyEntity(8),
            new EmptyEntity(9),
            new EmptyEntity(10)
        ], $this->repo->getAll());
    }

    public function testRepositorySubset()
    {
        $entities = $this->makeEntities(10);

        $this->repo->saveAll($entities);

        $subset = $this->repo->subset(
            EmptyEntity::criteria()
                ->where('id', '<=', 9)
                ->orderByDesc('id')
                ->skip(5)
                ->limit(3)
        );

        $this->assertEquals([
            new EmptyEntity(4),
            new EmptyEntity(3),
            new EmptyEntity(2),
        ], $subset->getAll());

        $subset->clear();

        $this->assertEquals([
            new EmptyEntity(1),
            //
            new EmptyEntity(5),
            new EmptyEntity(6),
            new EmptyEntity(7),
            new EmptyEntity(8),
            new EmptyEntity(9),
            new EmptyEntity(10)
        ], $this->repo->getAll());
    }

    public function testRepositorySubsetWithSpecificInstanceOf()
    {
        $entities = $this->makeEntities(10);

        $this->repo->saveAll($entities);

        $subset = $this->repo->subset(
            EmptyEntity::criteria()
                ->whereInstanceOf(EmptyEntitySubclass::class)
        );

        $this->assertEquals(EmptyEntitySubclass::class, $subset->getObjectType());
    }
}