<?php

namespace Dms\Core\Tests\Model\Object;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Model\ITypedObjectCollection;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\ObjectType;
use Dms\Core\Tests\Model\Object\Fixtures\BlankTypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TypedObjectStaticMethodTest extends CmsTestCase
{
    public function testType()
    {
        /** @var ObjectType $type */
        $type = BlankTypedObject::type();

        $this->assertInstanceOf(ObjectType::class, $type);
        $this->assertSame(BlankTypedObject::class, $type->getClass());
    }

    public function testCollection()
    {
        $collection = BlankTypedObject::collection([new BlankTypedObject()]);

        $this->assertInstanceOf(ITypedObjectCollection::class, $collection);
        $this->assertSame(BlankTypedObject::class, $collection->getObjectType());
        $this->assertSame(Type::object(BlankTypedObject::class), $collection->getElementType());
        $this->assertCount(1, $collection);
    }
}