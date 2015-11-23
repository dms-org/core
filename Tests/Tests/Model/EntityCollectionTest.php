<?php

namespace Iddigital\Cms\Core\Tests\Model;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\EntityCollection;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\ILoadCriteria;
use Iddigital\Cms\Core\Model\TypedCollection;
use Iddigital\Cms\Core\Tests\Model\Fixtures\SubObject;
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

    public function testMatchingCriteriaOffsets()
    {
        $objects = $this->collection->matching(
                $this->collection->criteria()->skip(2)->limit(2)
        );

        $this->assertEquals([10, 11], $this->getEntityIds($objects));
    }

    public function testPartialLoadCriteria()
    {
        $criteria = $this->collection->loadCriteria();

        $this->assertInstanceOf(ILoadCriteria::class, $criteria);
        $this->assertSame(TestEntity::class, $criteria->getClass()->getClassName());
    }

    public function testPartialLoadEntities()
    {
        $data = $this->collection->loadPartial(
                $this->collection->loadCriteria()
                        ->loadAll(['id', 'object.prop' => 'sub-prop'])
                        ->where('id', '>', 5)
        );

        $this->assertSame([
                ['id' => 10, 'sub-prop' => 'foo'],
                ['id' => 11, 'sub-prop' => null],
                ['id' => 12, 'sub-prop' => 'foo'],
        ], $data);
    }

    protected function loadNestedTestDataCollection()
    {
        return TestEntity::collection([
                TestEntity::withSubObjects([
                        new SubObject('abc', 5, [1, 2, 3]),
                        new SubObject('def', 10, [4, 5, 6]),
                        new SubObject('ghi', 15, [7, 8, 9]),
                ]),
                TestEntity::withSubObjects([
                        new SubObject('jkl', 10, [1, 2]),
                        new SubObject('mno', 20, [4, 5]),
                ]),
        ]);
    }

    public function testAverageInPartialCriteria()
    {
        $collection = $this->loadNestedTestDataCollection();

        $data = $collection->loadPartial(
                $collection->loadCriteria()
                        ->loadAll([
                                'objects.average(number)' => 'avg'
                        ])
        );

        $this->assertSame([
                ['avg' => 10.0],
                ['avg' => 15.0],
        ], $data);

    }

    public function testSumInPartialCriteria()
    {
        $collection = $this->loadNestedTestDataCollection();

        $data = $collection->loadPartial(
                $collection->loadCriteria()
                        ->loadAll([
                                'objects.sum(number)' => 'sum'
                        ])
        );

        $this->assertSame([
                ['sum' => 30],
                ['sum' => 30],
        ], $data);
    }

    public function testMaxInPartialCriteria()
    {
        $collection = $this->loadNestedTestDataCollection();

        $data = $collection->loadPartial(
                $collection->loadCriteria()
                        ->loadAll([
                                'objects.max(number)' => 'max'
                        ])
        );

        $this->assertSame([
                ['max' => 15],
                ['max' => 20],
        ], $data);
    }

    public function testMinInPartialCriteria()
    {
        $collection = $this->loadNestedTestDataCollection();

        $data = $collection->loadPartial(
                $collection->loadCriteria()
                        ->loadAll([
                                'objects.min(number)' => 'min'
                        ])
        );

        $this->assertSame([
                ['min' => 5],
                ['min' => 10],
        ], $data);
    }

    public function testCountInPartialCriteria()
    {
        $collection = $this->loadNestedTestDataCollection();

        $data = $collection->loadPartial(
                $collection->loadCriteria()
                        ->loadAll([
                                'objects.count()' => 'count'
                        ])
        );

        $this->assertSame([
                ['count' => 3],
                ['count' => 2],
        ], $data);
    }

    public function testFlattenInPartialCriteria()
    {
        $collection = $this->loadNestedTestDataCollection();

        $data = $collection->loadPartial(
                $collection->loadCriteria()
                        ->loadAll([
                                'objects.flatten(numbers)' => 'flat'
                        ])
        );

        $this->assertSame([
                ['flat' => [1, 2, 3, 4, 5, 6, 7, 8, 9]],
                ['flat' => [1, 2, 4, 5]],
        ], $data);
    }

    public function testChainedMethodPartialCriteria()
    {
        $collection = $this->loadNestedTestDataCollection();

        $data = $collection->loadPartial(
                $collection->loadCriteria()
                        ->loadAll([
                                'objects.max(number)'              => 'max-num',
                                'objects.flatten(numbers)'         => 'flat',
                                'objects.flatten(numbers).count()' => 'flat-count',
                        ])
        );

        $this->assertSame([
                ['max-num' => 15, 'flat' => [1, 2, 3, 4, 5, 6, 7, 8, 9], 'flat-count' => 9],
                ['max-num' => 20, 'flat' => [1, 2, 4, 5], 'flat-count' => 4],
        ], $data);
    }
}