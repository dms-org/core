<?php

namespace Iddigital\Cms\Core\Tests\Model;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Model\Criteria\Criteria;
use Iddigital\Cms\Core\Model\Criteria\SpecificationDefinition;
use Iddigital\Cms\Core\Model\EntityCollection;
use Iddigital\Cms\Core\Model\EntityNotFoundException;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Model\Type\ObjectType;
use Iddigital\Cms\Core\Tests\Model\Criteria\Fixtures\MockSpecification;
use Iddigital\Cms\Core\Tests\Model\Fixtures\SubObject;
use Iddigital\Cms\Core\Tests\Model\Fixtures\TestEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Id\EmptyEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class IEntitySetTest extends CmsTestCase
{
    /**
     * @var IEntitySet
     */
    protected $collection;

    protected function entityMock($id)
    {
        return new TestEntity($id, '', $id % 2 === 0 ? new SubObject('foo') : null);
    }

    public function setUp()
    {
        $this->collection = new EntityCollection(TestEntity::class, [
                $this->entityMock(1),
                $this->entityMock(5),
                $this->entityMock(10),
                $this->entityMock(11),
                $this->entityMock(12),
        ]);
    }

    public function testCanAddEntitiesThroughConstructor()
    {
        new EntityCollection(TestEntity::class, [
                $this->entityMock(1),
                $this->entityMock(2),
                $this->entityMock(3),
        ]);
    }

    public function testInvalidEntitiesInConstructorThrows()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        new EntityCollection(\DateTime::class, [
                $this->entityMock(1),
                new \stdClass(),
                $this->entityMock(2),
        ]);
    }

    public function testGetElementType()
    {
        /** @var ObjectType $type */
        $type = $this->collection->getElementType();

        $this->assertInstanceOf(ObjectType::class, $type);
        $this->assertSame(TestEntity::class, $type->getClass());
    }

    public function testGetEntityType()
    {
        $this->assertSame(TestEntity::class, $this->collection->getEntityType());
    }

    public function testGetAllAsArray()
    {
        $entities = $this->collection->getAll();

        $this->assertInternalType('array', $entities);
        $this->assertContainsOnlyInstancesOf(TestEntity::class, $entities);
        $this->assertEquals([1, 5, 10, 11, 12], $this->getEntityIds($entities));
    }

    public function testHas()
    {
        $this->assertTrue($this->collection->has(1));
        $this->assertTrue($this->collection->has(5));
        $this->assertTrue($this->collection->has(11));

        $this->assertFalse($this->collection->has(2));
        $this->assertFalse($this->collection->has(4));
        $this->assertFalse($this->collection->has(54));
    }

    public function testHasAll()
    {
        $this->assertTrue($this->collection->hasAll([1]));
        $this->assertTrue($this->collection->hasAll([1, 5, 10, 11, 12]));

        $this->assertFalse($this->collection->hasAll([4]));
        $this->assertFalse($this->collection->hasAll([1, 5, 10, 11, 12, 2]));
        $this->assertFalse($this->collection->hasAll([4534, 6456]));
    }

    public function testGetEntityById()
    {
        $entity = $this->collection->get(1);
        $this->assertInstanceOf(TestEntity::class, $entity);
        $this->assertSame(1, $entity->getId());
    }

    public function testGetEntityByValidIdThrows()
    {
        $this->setExpectedException(EntityNotFoundException::class);
        $this->collection->get(45354);
    }

    public function testTryGetEntityById()
    {
        $entity = $this->collection->tryGet(5);
        $this->assertInstanceOf(TestEntity::class, $entity);
        $this->assertSame(5, $entity->getId());
    }

    public function testTryGetEntityByValidIdReturnsNull()
    {
        $this->assertNull($this->collection->tryGet(45354));
    }

    public function testTryGetAllReturnsAllTheEntitiesWithTheIds()
    {
        $entities = $this->collection->tryGetAll([1, 10, 43, 12]);
        $this->assertInternalType('array', $entities);
        $this->assertSame([1, 10, 12], $this->getEntityIds($entities));
    }

    public function testEmptyCriteria()
    {
        $criteria = $this->collection->criteria();

        $this->assertInstanceOf(Criteria::class, $criteria);
        $this->assertSame(TestEntity::class, $criteria->getClass()->getClassName());
        $this->assertNotSame($criteria, $this->collection->criteria());
        $this->assertEquals($this->collection->getAll(), $this->collection->matching($criteria));
    }

    public function testInvalidCriteriaType()
    {
        $this->setExpectedException(TypeMismatchException::class);

        $this->collection->matching(SubObject::criteria());
    }

    public function testInvalidCriteriaTypeForCount()
    {
        $this->setExpectedException(TypeMismatchException::class);

        $this->collection->countMatching(SubObject::criteria());
    }

    public function testMatchingCriteriaConditions()
    {
        $criteria = $this->collection->criteria();

        $criteria->where('id', '>=', 5)
                ->whereInstanceOf(TestEntity::class);

        $this->assertSame([5, 10, 11, 12], $this->getEntityIds($this->collection->matching($criteria)));
    }

    public function testCountMatchingCriteriaConditions()
    {
        $criteria = $this->collection->criteria();

        $criteria->where('id', '>=', 5)
                ->whereInstanceOf(TestEntity::class);

        $this->assertSame(4, $this->collection->countMatching($criteria));
    }

    public function testMatchingCriteriaOrderings()
    {
        $criteria = $this->collection->criteria();

        $criteria->orderByDesc('id')->orderByAsc('prop');

        $this->assertSame([12, 11, 10, 5, 1], $this->getEntityIds($this->collection->matching($criteria)));
    }

    public function testMatchingCriteriaWithNestedProperties()
    {
        $criteria = $this->collection->criteria();

        $criteria->where('object.prop', '=', 'foo');

        $this->assertSame([10, 12], $this->getEntityIds($this->collection->matching($criteria)));
    }

    public function testMatchingCriteriaWithNestedPropertiesNulls()
    {
        $criteria = $this->collection->criteria();

        $criteria->where('object.prop', '=', null);

        $this->assertSame([1, 5, 11], $this->getEntityIds($this->collection->matching($criteria)));
    }

    public function testMatchingCriteriaOrderByWithNestedProperties()
    {
        $criteria = $this->collection->criteria();

        $criteria->orderByAsc('object.prop');

        $this->assertSame([1, 5, 11, 10, 12], $this->getEntityIds($this->collection->matching($criteria)));
    }

    public function testMatchingSpecification()
    {
        $spec = new MockSpecification(TestEntity::class, function (SpecificationDefinition $match) {
            $match->where('id', '>=', 5)
                    ->whereInstanceOf(TestEntity::class);
        });

        $this->assertSame([5, 10, 11, 12], $this->getEntityIds($this->collection->satisfying($spec)));
    }

    protected function getEntityIds($entities)
    {
        return array_values(array_map(function (TestEntity $i) {
            return $i->getId();
        }, $entities));
    }

    public function testContains()
    {
        $this->assertSame(true, $this->collection->contains($this->entityMock(1)));
        $this->assertSame(true, $this->collection->contains($this->entityMock(5)));

        $this->assertSame(false, $this->collection->contains($this->entityMock(7)));
        $this->assertSame(false, $this->collection->contains($this->entityMock(2)));

        $this->assertThrows(function () {
            $this->collection->contains(new EmptyEntity(1));
        }, TypeMismatchException::class);
    }

    public function testContainsAll()
    {
        $this->assertSame(true, $this->collection->containsAll([]));
        $this->assertSame(true, $this->collection->containsAll([$this->entityMock(1)]));
        $this->assertSame(true, $this->collection->containsAll([$this->entityMock(5)]));
        $this->assertSame(true, $this->collection->containsAll([$this->entityMock(1), $this->entityMock(5)]));

        $this->assertSame(false, $this->collection->containsAll([$this->entityMock(null)]));
        $this->assertSame(false, $this->collection->containsAll([$this->entityMock(3)]));
        $this->assertSame(false, $this->collection->containsAll([$this->entityMock(1), $this->entityMock(5), $this->entityMock(3)]));

        $this->assertThrows(function () {
            $this->collection->containsAll([$this->entityMock(1), $this->entityMock(2), new EmptyEntity(3)]);
        }, TypeMismatchException::class);
    }
}