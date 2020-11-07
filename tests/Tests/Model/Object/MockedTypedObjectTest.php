<?php

namespace Dms\Core\Tests\Model\Object;

use Dms\Core\Tests\Model\Object\Fixtures\BlankTypedObject;
use PHPUnit\Framework\MockObject\MockObject;

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
        $this->assertInstanceOf(MockObject::class, $this->object);
    }
}