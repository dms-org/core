<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping;

use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Model\Criteria\SpecificationDefinition;
use Iddigital\Cms\Core\Model\EntityNotFoundException;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Tests\Model\Criteria\Fixtures\MockSpecification;
use Iddigital\Cms\Core\Tests\Persistence\Db\Fixtures\MockEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Id\EmptyEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Id\EmptyMapper;

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
    }

    public function testGetAllEmpty()
    {
        $this->assertSame([], $this->repo->getAll());
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

    public function testGetInvalidId()
    {
        $entity = new EmptyEntity();
        $this->repo->save($entity);

        $this->assertSame(1, $entity->getId());

        $this->setExpectedException(EntityNotFoundException::class);
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

    public function testTryGetAll()
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
        $this->setExpectedException(TypeMismatchException::class);

        $this->repo->matching(MockEntity::criteria());
    }

    public function testInvalidCriteriaTypeForCount()
    {
        $this->setExpectedException(TypeMismatchException::class);

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
}