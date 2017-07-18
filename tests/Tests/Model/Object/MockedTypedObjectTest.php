<?php

namespace Dms\Core\Tests\Model\Object;

use Dms\Core\Tests\Model\Object\Fixtures\BlankTypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MockedTypedObjectTest extends TypedObjectTest
{
    /**
     * @var BlankTypedObject
     */
    protected $object;

    /**
     * @return BlankTypedObject
     */
    protected function buildObject()
    {
        return $this->createMock(BlankTypedObject::class);
    }

    public function testLoads()
    {
        $this->assertInstanceOf(BlankTypedObject::class, $this->object);
        $this->assertInstanceOf(\PHPUnit_Framework_MockObject_MockObject::class, $this->object);
    }
}