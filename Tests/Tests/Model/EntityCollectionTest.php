<?php

namespace Iddigital\Cms\Core\Tests\Model;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\EntityCollection;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\IPartialLoadCriteria;
use Iddigital\Cms\Core\Model\TypedCollection;
use Iddigital\Cms\Core\Tests\Model\Fixtures\TestEntity;

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

    public function testPartialLoadCriteria()
    {
        $criteria = $this->collection->partialCriteria();

        $this->assertInstanceOf(IPartialLoadCriteria::class, $criteria);
        $this->assertSame(TestEntity::class, $criteria->getClass()->getClassName());
    }

    public function testPartialLoadEntities()
    {
        $data = $this->collection->loadPartial(
                $this->collection->partialCriteria()
                        ->loadAll(['id', 'object.prop' => 'sub-prop'])
                        ->where('id', '>', 5)
        );

        $this->assertSame([
                ['id' => 10, 'sub-prop' => 'foo'],
                ['id' => 11, 'sub-prop' => null],
                ['id' => 12, 'sub-prop' => 'foo'],
        ], $data);
    }
}