<?php

namespace Dms\Core\Tests\Model\Object;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\ValueObjectCollection;
use Dms\Core\Tests\Model\Object\Fixtures\TestMutableValueObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MutableValueObjectTest extends CmsTestCase
{
    public function testValueObjectCollection()
    {
        $collection = TestMutableValueObject::collection([new TestMutableValueObject()]);

        $this->assertInstanceOf(ValueObjectCollection::class, $collection);
        $this->assertSame(TestMutableValueObject::class, $collection->getObjectType());
        $this->assertEquals(Type::object(TestMutableValueObject::class), $collection->getElementType());
        $this->assertCount(1, $collection);
    }

    public function testCollectionType()
    {
        $this->assertTrue(TestMutableValueObject::collectionType()->isOfType(TestMutableValueObject::collection()));

        $this->assertEquals(Type::collectionOf(TestMutableValueObject::type(), ValueObjectCollection::class), TestMutableValueObject::collectionType());
    }

    public function testValueObjectsAreImmutableByDefault()
    {
        $object = new TestMutableValueObject();

        $object->one = 'abc';

        $this->assertSame('abc', $object->one);

        $object->one = '123';

        $this->assertSame('123', $object->one);
    }

    public function testObjectHash()
    {
        $object1      = new TestMutableValueObject();
        $object1->one = 'abc';
        $object2      = new TestMutableValueObject();
        $object2->one = 'def';
        $object3      = new TestMutableValueObject();
        $object3->one = 'abc';

        $this->assertSame($object1->getObjectHash(), $object1->getObjectHash());
        $this->assertSame($object1->getObjectHash(), $object3->getObjectHash());

        $this->assertNotEquals($object1->getObjectHash(), $object2->getObjectHash());
        $this->assertNotEquals($object3->getObjectHash(), $object2->getObjectHash());
    }
}