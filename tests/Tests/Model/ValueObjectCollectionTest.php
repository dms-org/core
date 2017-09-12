<?php

namespace Dms\Core\Tests\Model;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Model\Criteria\SpecificationDefinition;
use Dms\Core\Model\IValueObject;
use Dms\Core\Model\ObjectNotFoundException;
use Dms\Core\Model\Subset\MutableObjectSetSubset;
use Dms\Core\Model\TypedCollection;
use Dms\Core\Model\ValueObjectCollection;
use Dms\Core\Tests\Model\Fixtures\SubObject;
use Dms\Core\Tests\Model\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ValueObjectCollectionTest extends CmsTestCase
{
    public function testValidType()
    {
        new  ValueObjectCollection(IValueObject::class);;
    }

    public function testInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        new  ValueObjectCollection(\stdClass::class);
    }

    public function testProjectionReturnsTypedCollection()
    {
        $valueObjects = SubObject::collection([new SubObject('data')]);
        $props        = $valueObjects->select(function (SubObject $object) {
            return $object->prop;
        });

        $this->assertInstanceOf(TypedCollection::class, $props);
        $this->assertNotInstanceOf(ValueObjectCollection::class, $props);

        $this->assertEquals(['data'], $props->asArray());
    }

    public function testContains()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
        ]);

        $this->assertSame(true, $collection->contains($object1));
        $this->assertSame(true, $collection->contains($object2));
        $this->assertSame(true, $collection->contains(clone $object1));
        $this->assertSame(true, $collection->contains(new SubObject('foo')));

        $this->assertSame(false, $collection->contains(new SubObject('abc')));

        $this->assertThrows(function () use ($collection) {
            $collection->contains(new TestEntity(1));
        }, TypeMismatchException::class);
    }

    public function testContainsAll()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
        ]);
        $this->assertSame(true, $collection->containsAll([]));
        $this->assertSame(true, $collection->containsAll([$object1]));
        $this->assertSame(true, $collection->containsAll([$object2]));
        $this->assertSame(true, $collection->containsAll([$object1, $object2]));
        $this->assertSame(true, $collection->containsAll([clone $object1]));
        $this->assertSame(true, $collection->containsAll([new SubObject('bar')]));

        $this->assertSame(false, $collection->containsAll([$object1, $object2, new SubObject('abc')]));

        $this->assertThrows(function () use ($collection, $object1, $object2) {
            $collection->containsAll([$object1, $object2, new TestEntity(3)]);
        }, TypeMismatchException::class);
    }

    public function testHas()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
        ]);

        $this->assertSame(true, $collection->has(0));
        $this->assertSame(true, $collection->has(1));

        $this->assertSame(false, $collection->has(-1));
        $this->assertSame(false, $collection->has(2));

        $collection[] = new SubObject('abc');

        $this->assertSame(true, $collection->has(2));
    }

    public function testHasAll()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
        ]);

        $this->assertSame(true, $collection->hasAll([0, 1]));
        $this->assertSame(true, $collection->hasAll([0]));
        $this->assertSame(true, $collection->hasAll([1]));

        $this->assertSame(false, $collection->hasAll([0, 1, 2]));
        $this->assertSame(false, $collection->hasAll([2]));
    }

    public function testGet()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
        ]);

        $this->assertSame($object1, $collection->get(0));
        $this->assertSame($object2, $collection->get(1));

        $this->assertThrows(function () use ($collection) {
            $collection->get(2);
        }, ObjectNotFoundException::class);

        $collection[] = $object3 = new SubObject('abc');

        $this->assertSame($object3, $collection->get(2));
    }

    public function testGetAllById()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
        ]);

        $this->assertSame([$object1, $object2], $collection->getAllById([0, 1]));
        $this->assertSame([$object1], $collection->getAllById([0]));
        $this->assertSame([$object2], $collection->getAllById([1]));

        $this->assertThrows(function () use ($collection) {
            $collection->getAllById([0, 1, 2]);
        }, ObjectNotFoundException::class);

        $this->assertThrows(function () use ($collection) {
            $collection->getAllById([2]);
        }, ObjectNotFoundException::class);
    }

    public function testTryGet()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
        ]);

        $this->assertSame($object1, $collection->tryGet(0));
        $this->assertSame($object2, $collection->tryGet(1));

        $this->assertSame(null, $collection->tryGet(3));
    }


    public function testTryGetAll()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
        ]);

        $this->assertSame([$object1, $object2], $collection->tryGetAll([0, 1]));
        $this->assertSame([$object1], $collection->tryGetAll([0]));
        $this->assertSame([$object2], $collection->tryGetAll([1]));

        $this->assertSame([$object1, $object2], $collection->tryGetAll([0, 1, 2]));
        $this->assertSame([], $collection->tryGetAll([2]));
    }

    public function testIsSerializable()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
        ]);

        $this->assertEquals($collection, unserialize(serialize($collection)));
    }

    public function testCriteriaMaintainsIndexes()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
            $object3 = new SubObject('baz'),
        ]);

        $this->assertSame([
            1 => $object2,
            2 => $object3,
        ], $collection->matching(
            $collection->criteria()
                ->whereStringContains('prop', 'ba')
        ));
    }

    public function testClear()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
        ]);

        $collection->clear();

        $this->assertSame([], $collection->getAll());
    }

    public function testSave()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
        ]);

        $collection->save($object3 = new SubObject('baz'));

        $this->assertSame([$object1, $object2, $object3], $collection->getAll());
    }

    public function testSaveAll()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
        ]);

        $collection->saveAll([$object3 = new SubObject('baz'), $object4 = new SubObject('qux')]);

        $this->assertSame([$object1, $object2, $object3, $object4], $collection->getAll());
    }

    public function testRemove()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
        ]);

        $collection->remove($object1);

        $this->assertSame([$object2], $collection->getAll());
    }

    public function testRemoveById()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
        ]);

        $collection->removeById(1);

        $this->assertSame([$object1], $collection->getAll());
    }

    public function testRemoveAll()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
        ]);

        $collection->removeAll([$object1, $object2]);

        $this->assertSame([], $collection->getAll());
    }

    public function testRemoveAllById()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
        ]);

        $collection->removeAllById([0, 1]);

        $this->assertSame([], $collection->getAll());
    }

    public function testUpdate()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
        ]);

        $collection->update($object2, $object3 = new SubObject('baz'));

        $this->assertSame([$object1, $object3], $collection->getAll());

        $this->assertThrows(function () use ($collection) {
            $collection->update(new SubObject('baz'), new SubObject('123'));
        }, InvalidArgumentException::class);
    }

    public function testUpdateAtIndex()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
        ]);

        $collection->updateAtIndex(1, $object3 = new SubObject('baz'));

        $this->assertSame([$object1, $object3], $collection->getAll());

        $this->assertThrows(function () use ($collection) {
            $collection->updateAtIndex(5, new SubObject('123'));
        }, InvalidArgumentException::class);
    }

    public function testGetObjectId()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
        ]);

        $this->assertSame(0, $collection->getObjectId($object1));
        $this->assertSame(1, $collection->getObjectId($object2));

        $this->assertThrows(function () use ($collection) {
            $collection->getObjectId(new SubObject('123'));
        }, InvalidArgumentException::class);
    }

    public function testInsertAt()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
        ]);

        $collection->insertAt(0, $newObject1 = new SubObject('1'));

        $this->assertSame([$newObject1, $object1, $object2], $collection->getAll());

        $collection->insertAt(2, $newObject2 = new SubObject('1'));

        $this->assertSame([$newObject1, $object1, $newObject2, $object2], $collection->getAll());

        $this->assertThrows(function () use ($collection) {
            $collection->insertAt(5, new SubObject('123'));
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($collection) {
            $collection->insertAt(0, 'abc');
        }, InvalidArgumentException::class);
    }

    public function testAddRange()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo'),
            $object2 = new SubObject('bar'),
        ]);

        $collection->addRange([$object1, $object2]);

        $this->assertThrows(function () use ($collection) {
            $collection->addRange([new SubObject('fsd'), 453]);
        }, InvalidArgumentException::class);
    }

    public function testRemoveMatching()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo', 3),
            $object2 = new SubObject('bar', 2),
            $object3 = new SubObject('aFOOb', 1),
            $object4 = new SubObject('quzFoo', 0),
        ]);

        $collection->removeMatching(
            $collection->criteria()
                ->whereStringContainsCaseInsensitive('prop', 'foo')
                ->skip(1)
        );

        $this->assertSame([$object1, $object2], $collection->getAll());
    }

    public function testSubset()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo', 3),
            $object2 = new SubObject('bar', 2),
            $object3 = new SubObject('aFOOb', 1),
            $object4 = new SubObject('quzFoo', 0),
        ]);

        $subset = $collection->subset(
            $collection->criteria()
                ->whereStringContainsCaseInsensitive('prop', 'foo')
                ->orderByAsc('number')
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
            SubObject::specification(function (SpecificationDefinition $match) {
                $match->where('number', '>=', 2)->where('number', '<=', 3);
            })
        ));
    }

    public function testNestedSubset()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo', 3),
            $object2 = new SubObject('bar', 2),
            $object3 = new SubObject('aFOOb', 1),
            $object4 = new SubObject('quzFoo', 0),
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
        $this->assertSame([$object4], $subset->getAllById([3]));
        $this->assertSame(false, $subset->containsAll($collection->asArray()));
        $this->assertSame(true, $subset->containsAll([$object4]));

        $this->assertThrows(function () use ($subset) {
            $subset->getAllById([1, 2, 3]);
        }, ObjectNotFoundException::class);
    }

    public function testSubsetIdentityMethods()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo', 3),
            $object2 = new SubObject('bar', 2),
            $object3 = new SubObject('aFOOb', 1),
            $object4 = new SubObject('quzFoo', 0),
        ]);

        $subset = $collection->subset(
            $collection->criteria()
                ->whereStringContainsCaseInsensitive('prop', 'foo')
        );

        $this->assertSame(3, $subset->getObjectId($object4));
        $this->assertSame(true, $subset->has(3));
        $this->assertSame(false, $subset->has(1));

        $this->assertSame($object4, $subset->get(3));
        $this->assertThrows(function () use ($subset) {
            $subset->get(1);
        }, ObjectNotFoundException::class);

        $this->assertSame($object4, $subset->tryGet(3));
        $this->assertSame(null, $subset->tryGet(1));
    }

    public function testSubsetRemoveById()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo', 3),
            $object2 = new SubObject('bar', 2),
            $object3 = new SubObject('aFOOb', 1),
            $object4 = new SubObject('quzFoo', 0),
        ]);

        $subset = $collection->subset(
            $collection->criteria()
                ->whereStringContainsCaseInsensitive('prop', 'foo')
        );

        $subset->removeById(3);

        $this->assertSame(2, $subset->count());
        $this->assertSame([$object1, 2 => $object3], $subset->getAll());
        $this->assertSame(3, $collection->count());
        $this->assertSame([$object1, $object2, $object3], $collection->getAll());
    }

    public function testSubsetClear()
    {
        $collection = SubObject::collection([
            $object1 = new SubObject('foo', 3),
            $object2 = new SubObject('bar', 2),
            $object3 = new SubObject('aFOOb', 1),
            $object4 = new SubObject('quzFoo', 0),
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
        $collection = SubObject::collection([
            $object1 = new SubObject('foo', 3),
            $object2 = new SubObject('bar', 2),
            $object3 = new SubObject('aFOOb', 1),
            $object4 = new SubObject('quzFoo', 0),
        ]);

        $subset = $collection->subset(
            $collection->criteria()
                ->whereStringContainsCaseInsensitive('prop', 'foo')
        );

        $subset->removeMatching(
            $collection->criteria()
                ->whereIn('number', [2, 0])
        );

        $this->assertSame([$object1, 2 => $object3], $subset->getAll());
        $this->assertSame([$object1, $object2, $object3], $collection->getAll());
    }
}