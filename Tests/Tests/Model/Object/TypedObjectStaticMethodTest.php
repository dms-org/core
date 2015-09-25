<?php

namespace Iddigital\Cms\Core\Tests\Model\Object;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\ITypedObjectCollection;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\ObjectType;
use Iddigital\Cms\Core\Tests\Model\Object\Fixtures\BlankTypedObject;

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
        $collection = BlankTypedObject::collection();

        $this->assertInstanceOf(ITypedObjectCollection::class, $collection);
        $this->assertSame(BlankTypedObject::class, $collection->getObjectType());
        $this->assertSame(Type::object(BlankTypedObject::class), $collection->getElementType());
    }
}