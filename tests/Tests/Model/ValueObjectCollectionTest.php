<?php

namespace Dms\Core\Tests\Model;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Model\IValueObject;
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
        $this->setExpectedException(InvalidArgumentException::class);
        new  ValueObjectCollection(\stdClass::class);
    }

    public function testProjectionReturnsTypedCollection()
    {
        $valueObjects = SubObject::collection([new SubObject('data')]);
        $props = $valueObjects->select(function (SubObject $object) {
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
}