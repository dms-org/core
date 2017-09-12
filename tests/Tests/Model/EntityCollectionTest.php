<?php

namespace Dms\Core\Tests\Model;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Criteria\SpecificationDefinition;
use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\EntityNotFoundException;
use Dms\Core\Model\IEntity;
use Dms\Core\Model\ILoadCriteria;
use Dms\Core\Model\ObjectNotFoundException;
use Dms\Core\Model\Subset\MutableObjectSetSubset;
use Dms\Core\Model\TypedCollection;
use Dms\Core\Tests\Model\Fixtures\SubObject;
use Dms\Core\Tests\Model\Fixtures\TestEntity;
use Dms\Core\Tests\Model\Object\Fixtures\BlankEntity;

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
        $this->expectException(InvalidArgumentException::class);
        $this->collection[] = new \stdClass();
    }

    public function testInvalidEntityType()
    {
        $this->expectException(InvalidArgumentException::class);
        new EntityCollection(\stdClass::class);
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

    public function testGetObjectId()
    {
        $entity = $this->entityMock(5);
        $this->assertSame(5, $this->collection->getObjectId($entity));

        $this->assertThrows(function () {
            $this->collection->getObjectId($this->entityMock(null));
        }, InvalidArgumentException::class);
    }

    public function testGetObjectIdWithEntityWithoutIds()
    {
        $entity1             = $this->entityMock(null);
        $entity2             = $this->entityMock(null);
        $this->collection[] = $entity1;
        $this->collection[] = $entity2;

        $this->assertSame(EntityCollection::ENTITY_WITHOUT_ID_PREFIX . 0, $this->collection->getObjectId($entity1));
        $this->assertSame(EntityCollection::ENTITY_WITHOUT_ID_PREFIX . 1, $this->collection->getObjectId($entity2));
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
        $data = $this->collection->loadMatching(
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

        $data = $collection->loadMatching(
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

        $data = $collection->loadMatching(
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

        $data = $collection->loadMatching(
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

        $data = $collection->loadMatching(
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

        $data = $collection->loadMatching(
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

        $data = $collection->loadMatching(
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

        $data = $collection->loadMatching(
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

    public function testCriteriaWithWhereHasAllCondition()
    {
        $collection = $this->loadNestedTestDataCollection();
        $data       = $collection->matching(
            $collection->criteria()
                ->whereHasAll('objects', SubObject::specification(function (SpecificationDefinition $spec) {
                    $spec->where('number', '>=', 10);
                }))
        );

        $this->assertEquals([1 => $collection->skip(1)->first()], $data);
    }

    public function testCriteriaWithWhereHasAnyCondition()
    {
        $collection = $this->loadNestedTestDataCollection();
        $data       = $collection->matching(
            $collection->criteria()
                ->whereHasAny('objects', SubObject::specification(function (SpecificationDefinition $spec) {
                    $spec->where('number', '>=', 10);
                }))
        );

        $this->assertEquals([$collection->first(), $collection->skip(1)->first()], $data);
    }

    public function testSelfCondition()
    {
        $data = $this->collection->matching(
            $this->collection->criteria()->where('this', '=', $this->entityMock(1))
        );

        $this->assertEquals([$this->entityMock(1)], $data);
    }

    public function testSelfConditionWithWhereIn()
    {
        $data = $this->collection->matching(
                $this->collection->criteria()->whereIn('this', [$this->entityMock(1), $this->entityMock(10)])
        );

        $this->assertEquals([$this->entityMock(1), $this->entityMock(10)], array_values($data));
    }

    public function testIsSerializable()
    {
        $collection = $this->collection;

        $this->assertEquals($collection, unserialize(serialize($collection)));
    }

    public function testIdStringContainsCriteria()
    {
        $data = $this->collection->matching(
                $this->collection->criteria()->whereStringContains('id', '1')
        );

        $this->assertEquals([
                $this->entityMock(1),
                $this->entityMock(10),
                $this->entityMock(11),
                $this->entityMock(12),
        ], array_values($data));
    }

    public function testIdStringContainsCaseInsensitiveCriteria()
    {
        $data = $this->collection->matching(
                $this->collection->criteria()->whereStringContainsCaseInsensitive('id', '1')
        );

        $this->assertEquals([
                $this->entityMock(1),
                $this->entityMock(10),
                $this->entityMock(11),
                $this->entityMock(12),
        ], array_values($data));
    }

    public function testObjectIdWithEntityWithoutId()
    {
        $this->collection = new EntityCollection(IEntity::class);

        $entity             = $this->entityMock(null);
        $this->collection[] = $entity;

        $id = $this->collection->getObjectId($entity);

        $this->assertSame(1, $this->collection->count());
        $this->assertSame(true, $this->collection->contains($entity));
        $this->assertSame(true, $this->collection->has($id));
        $this->assertSame($entity, $this->collection->get($id));
        $this->assertSame($entity, $this->collection->tryGet($id));
        $this->assertSame([$id => $entity], $this->collection->getAllById([$id]));
        $this->assertSame([$id => $entity], $this->collection->tryGetAll([$id]));

        $this->collection->removeById($id);

        $this->assertSame(false, $this->collection->contains($entity));
        $this->assertSame(false, $this->collection->has($id));
        $this->assertSame(null, $this->collection->tryGet($id));
        $this->assertSame([], $this->collection->tryGetAll([$id]));
    }

    public function testSubset()
    {
        $collection = TestEntity::collection([
            $object1 = new TestEntity(3, 'foo'),
            $object2 = new TestEntity(2, 'bar'),
            $object3 = new TestEntity(1, 'aFOOb'),
            $object4 = new TestEntity(0, 'quzFoo'),
        ]);

        $subset = $collection->subset(
            $collection->criteria()
                ->whereStringContainsCaseInsensitive('prop', 'foo')
                ->orderByAsc('id')
        );

        $this->assertInstanceOf(MutableObjectSetSubset::class, $subset);
        $this->assertSame(3, $subset->count());
        $this->assertSame([$object4, $object3, $object1], $subset->getAll());
        $this->assertSame(false, $subset->containsAll($collection->asArray()));
        $this->assertSame(true, $subset->containsAll([$object1, $object3, $object4]));

        $this->assertSame(0, $subset->countMatching(
            $subset->criteria()
                ->where('prop', '=', 'bar')
        ));

        $this->assertSame([], $subset->matching(
            $subset->criteria()
                ->where('prop', '=', 'bar')
        ));

        $this->assertSame(1, $subset->countMatching(
            $subset->criteria()
                ->where('prop', '=', 'aFOOb')
        ));

        $this->assertSame([$object3], $subset->matching(
            $subset->criteria()
                ->where('prop', '=', 'aFOOb')
        ));

        $this->assertSame([$object1], $subset->satisfying(
            TestEntity::specification(function (SpecificationDefinition $match) {
                $match->where('id', '>=', 2)->where('id', '<=', 3);
            })
        ));
    }

    public function testNestedSubset()
    {
        $collection = TestEntity::collection([
            $object1 = new TestEntity(3, 'foo'),
            $object2 = new TestEntity(2, 'bar'),
            $object3 = new TestEntity(1, 'aFOOb'),
            $object4 = new TestEntity(0, 'quzFoo'),
        ]);

        $subset = $collection->subset(
            $collection->criteria()
                ->whereStringContainsCaseInsensitive('prop', 'foo')
        )->subset(
            $collection->criteria()
                ->whereStringContainsCaseInsensitive('prop', 'q')
        );

        $this->assertInstanceOf(MutableObjectSetSubset::class, $subset);
        $this->assertSame(1, $subset->count());
        $this->assertSame([3 => $object4], $subset->getAll());
        $this->assertSame([$object4], $subset->getAllById([0]));
        $this->assertSame(false, $subset->containsAll($collection->asArray()));
        $this->assertSame(true, $subset->containsAll([$object4]));

        $this->assertThrows(function () use ($subset) {
            $subset->getAllById([1, 2, 3]);
        }, EntityNotFoundException::class);
    }

    public function testSubsetIdentityMethods()
    {
        $collection = TestEntity::collection([
            $object1 = new TestEntity(3, 'foo'),
            $object2 = new TestEntity(2, 'bar'),
            $object3 = new TestEntity(1, 'aFOOb'),
            $object4 = new TestEntity(0, 'quzFoo'),
        ]);

        $subset = $collection->subset(
            $collection->criteria()
                ->whereStringContainsCaseInsensitive('prop', 'foo')
        );

        $this->assertSame(0, $subset->getObjectId($object4));
        $this->assertSame(true, $subset->has(3));
        $this->assertSame(false, $subset->has(2));

        $this->assertSame($object1, $subset->get(3));
        $this->assertThrows(function () use ($subset) {
            $subset->get(2);
        }, EntityNotFoundException::class);

        $this->assertSame($object1, $subset->tryGet(3));
        $this->assertSame(null, $subset->tryGet(2));
    }

    public function testSubsetRemoveById()
    {
        $collection = TestEntity::collection([
            $object1 = new TestEntity(3, 'foo'),
            $object2 = new TestEntity(2, 'bar'),
            $object3 = new TestEntity(1, 'aFOOb'),
            $object4 = new TestEntity(0, 'quzFoo'),
        ]);

        $subset = $collection->subset(
            $collection->criteria()
                ->whereStringContainsCaseInsensitive('prop', 'foo')
        );

        $subset->removeById(3);

        $this->assertSame(2, $subset->count());
        $this->assertSame([2 => $object3, 3 => $object4], $subset->getAll());
        $this->assertSame(3, $collection->count());
        $this->assertSame([$object2, $object3, $object4], $collection->getAll());
    }

    public function testSubsetClear()
    {
        $collection = TestEntity::collection([
            $object1 = new TestEntity(3, 'foo'),
            $object2 = new TestEntity(2, 'bar'),
            $object3 = new TestEntity(1, 'aFOOb'),
            $object4 = new TestEntity(0, 'quzFoo'),
        ]);

        $subset = $collection->subset(
            $collection->criteria()
                ->whereStringContainsCaseInsensitive('prop', 'foo')
        );

        $subset->clear();

        $this->assertSame([], $subset->getAll());
        $this->assertSame([$object2], $collection->getAll());
    }

    public function testSubsetRemoveMatching()
    {
        $collection = TestEntity::collection([
            $object1 = new TestEntity(3, 'foo'),
            $object2 = new TestEntity(2, 'bar'),
            $object3 = new TestEntity(1, 'aFOOb'),
            $object4 = new TestEntity(0, 'quzFoo'),
        ]);

        $subset = $collection->subset(
            $collection->criteria()
                ->whereStringContainsCaseInsensitive('prop', 'foo')
        );

        $subset->removeMatching(
            $collection->criteria()
                ->whereIn('id', [2, 0])
        );

        $this->assertSame([$object1, 2 => $object3], $subset->getAll());
        $this->assertSame([$object1, $object2, $object3], $collection->getAll());
    }
}