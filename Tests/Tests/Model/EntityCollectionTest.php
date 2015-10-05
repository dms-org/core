<?php

namespace Iddigital\Cms\Core\Tests\Model;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\EntityCollection;
use Iddigital\Cms\Core\Model\EntityIdCollection;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\TypedCollection;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityCollectionTest extends IEntitySetTest
{
    /**
     * @var EntityCollection
     */
    protected $collection;

    public function testAllowsValidValue()
    {
        $this->collection[] = $this->entityMock(43);
    }

    public function testOffsetSetThrowsOnInvalidValue()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->collection[] = new \stdClass();
    }

    public function testInvalidEntityType()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        new  EntityCollection(\stdClass::class);
    }

    public function testCollectionUpdatesWhenItIsMutated()
    {
        $this->collection->take(2)->clear();

        $this->assertSame([10, 11, 12], $this->getEntityIds($this->collection->getAll()));
    }

    public function testGetAllAfterAppending()
    {
        $this->collection[] = $this->entityMock(20);
        $entities           = $this->collection->getAll();

        $this->assertEquals([1, 5, 10, 11, 12, 20], $this->getEntityIds($entities));
    }

    public function testProjectionReturnsTypedCollection()
    {
        $ids = $this->collection->select(function (IEntity $entity) {
            return $entity->getId();
        });

        $this->assertInstanceOf(TypedCollection::class, $ids);
        $this->assertNotInstanceOf(EntityCollection::class, $ids);

        $this->assertEquals([1, 5, 10, 11, 12], $ids->asArray());
    }
}