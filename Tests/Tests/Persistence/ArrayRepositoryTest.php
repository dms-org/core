<?php

namespace Dms\Core\Tests\Model;

use Dms\Core\Persistence\ArrayRepository;
use Dms\Core\Tests\Model\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayRepositoryTest extends IEntitySetTest
{
    /**
     * @var ArrayRepository
     */
    protected $collection;

    public function setUp()
    {
        parent::setUp();

        $this->collection = new ArrayRepository($this->collection);
    }

    public function testClear()
    {
        $this->collection->clear();

        $this->assertSame([], $this->collection->getAll());
    }

    public function testSave()
    {
        $this->collection->save($this->entityMock(20));

        $this->assertTrue($this->collection->has(20));
        $this->assertSame([1, 5, 10, 11, 12, 20], $this->getEntityIds($this->collection->getAll()));
    }

    public function testSaveAll()
    {
        $this->collection->saveAll([$this->entityMock(20), $this->entityMock(25)]);

        $this->assertTrue($this->collection->has(20));
        $this->assertTrue($this->collection->has(25));
        $this->assertSame([1, 5, 10, 11, 12, 20, 25], $this->getEntityIds($this->collection->getAll()));
    }

    public function testSaveWithNoId()
    {
        // Next free id is 13
        $entity = new TestEntity(null, '');

        $this->collection->save($entity);

        $this->assertSame(13, $entity->getId());
        $this->assertTrue($this->collection->has(13));
        $this->assertSame([1, 5, 10, 11, 12, 13], $this->getEntityIds($this->collection->getAll()));
    }

    public function testRemove()
    {
        $this->collection->remove($this->entityMock(10));

        $this->assertFalse($this->collection->has(10));
        $this->assertSame([1, 5, 11, 12], $this->getEntityIds($this->collection->getAll()));
    }

    public function testRemoveById()
    {
        $this->collection->removeById(10);

        $this->assertFalse($this->collection->has(10));
        $this->assertSame([1, 5, 11, 12], $this->getEntityIds($this->collection->getAll()));
    }

    public function testRemoveAll()
    {
        $this->collection->removeAll([$this->entityMock(10), $this->entityMock(11)]);

        $this->assertFalse($this->collection->has(10));
        $this->assertFalse($this->collection->has(11));
        $this->assertSame([1, 5, 12], $this->getEntityIds($this->collection->getAll()));
    }

    public function testRemoveAllById()
    {
        $this->collection->removeAllById([10, 11]);

        $this->assertFalse($this->collection->has(10));
        $this->assertFalse($this->collection->has(11));
        $this->assertSame([1, 5, 12], $this->getEntityIds($this->collection->getAll()));
    }

    public function testContains()
    {
        $this->assertTrue($this->collection->contains($this->collection->get(1)));

        $this->assertFalse($this->collection->contains(new TestEntity(100)));
    }

    public function testContainAll()
    {
        $this->assertTrue($this->collection->containsAll([$this->collection->get(1)]));

        $this->assertFalse($this->collection->containsAll([new TestEntity(100)]));
    }
}