<?php

namespace Dms\Core\Tests\Model;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Model\ObjectCollection;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Tests\Form\Field\Processor\Validator\Fixtures\TestEntity;
use Dms\Core\Tests\Model\Object\Fixtures\BlankTypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectCollectionTest extends CmsTestCase
{
    /**
     * @var ObjectCollection
     */
    protected $collection;

    public function setUp()
    {
        $this->collection = BlankTypedObject::collection();
    }

    public function testCollectionType()
    {
        $this->assertEquals(Type::collectionOf(BlankTypedObject::type(), ObjectCollection::class), BlankTypedObject::collectionType());
    }

    public function testContains()
    {
        $this->collection = BlankTypedObject::collection([
                $object1 = new BlankTypedObject(),
                $object2 = new BlankTypedObject(),
        ]);
        
        $this->assertSame(true, $this->collection->contains($object1));
        $this->assertSame(true, $this->collection->contains($object2));

        $this->assertSame(false, $this->collection->contains(clone $object1));
        $this->assertSame(false, $this->collection->contains(new BlankTypedObject()));

        $this->assertThrows(function () {
            $this->collection->contains(new TestEntity(1));
        }, TypeMismatchException::class);
    }

    public function testContainsAll()
    {
        $this->collection = BlankTypedObject::collection([
                $object1 = new BlankTypedObject(),
                $object2 = new BlankTypedObject(),
        ]);
        
        $this->assertSame(true, $this->collection->containsAll([]));
        $this->assertSame(true, $this->collection->containsAll([$object1]));
        $this->assertSame(true, $this->collection->containsAll([$object2]));
        $this->assertSame(true, $this->collection->containsAll([$object1, $object2]));

        $this->assertSame(false, $this->collection->containsAll([clone $object1]));
        $this->assertSame(false, $this->collection->containsAll([new BlankTypedObject()]));
        $this->assertSame(false, $this->collection->containsAll([$object1, $object2, new BlankTypedObject()]));

        $this->assertThrows(function () use ($object1, $object2) {
            $this->collection->containsAll([$object1, $object2, new TestEntity(3)]);
        }, TypeMismatchException::class);
    }

    public function testIsSerializable()
    {
        $collection = BlankTypedObject::collection([
                $object1 = new BlankTypedObject(),
                $object2 = new BlankTypedObject(),
        ]);

        $this->assertEquals($collection, unserialize(serialize($collection)));
    }
}