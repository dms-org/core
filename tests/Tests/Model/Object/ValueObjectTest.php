<?php

namespace Dms\Core\Tests\Model\Object;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Model\IValueObjectCollection;
use Dms\Core\Model\Object\ImmutablePropertyException;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\ValueObjectCollection;
use Dms\Core\Tests\Model\Object\Fixtures\TestValueObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ValueObjectTest extends CmsTestCase
{
    public function testValueObjectCollection()
    {
        $collection = TestValueObject::collection([new TestValueObject()]);

        $this->assertInstanceOf(ValueObjectCollection::class, $collection);
        $this->assertSame(TestValueObject::class, $collection->getObjectType());
        $this->assertEquals(Type::object(TestValueObject::class), $collection->getElementType());
        $this->assertCount(1, $collection);
    }

    public function testCollectionType()
    {
        $this->assertTrue(TestValueObject::collectionType()->isOfType(TestValueObject::collection()));

        $this->assertEquals(Type::collectionOf(TestValueObject::type(), ValueObjectCollection::class), TestValueObject::collectionType());
    }

    public function testValueObjectsAreImmutableByDefault()
    {
        $object = new TestValueObject();

        $object->one = 'abc';

        $this->expectException(ImmutablePropertyException::class);

        $object->one = '123';
    }

    public function testObjectHash()
    {
        $object1 = new TestValueObject();
        $object1->one = 'abc';
        $object2 = new TestValueObject();
        $object2->one = 'def';
        $object3 = new TestValueObject();
        $object3->one = 'abc';

        $this->assertSame($object1->getObjectHash(), $object1->getObjectHash());
        $this->assertSame($object1->getObjectHash(), $object3->getObjectHash());

        $this->assertNotEquals($object1->getObjectHash(), $object2->getObjectHash());
        $this->assertNotEquals($object3->getObjectHash(), $object2->getObjectHash());
    }
}