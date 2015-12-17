<?php

namespace Iddigital\Cms\Core\Tests\Model\Object;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\IValueObjectCollection;
use Iddigital\Cms\Core\Model\Object\ImmutablePropertyException;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\ValueObjectCollection;
use Iddigital\Cms\Core\Tests\Model\Object\Fixtures\TestValueObject;

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

        $this->setExpectedException(ImmutablePropertyException::class);

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