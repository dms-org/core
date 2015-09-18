<?php

namespace Iddigital\Cms\Core\Tests\Model\Object;

use Iddigital\Cms\Core\Tests\Model\Object\Fixtures\DefaultPropertyValues;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TypedObjectDefaultValuesTest extends TypedObjectTest
{
    /**
     * @var DefaultPropertyValues
     */
    protected $object;

    /**
     * @return DefaultPropertyValues
     */
    protected function buildObject()
    {
        return DefaultPropertyValues::build();
    }

    public function testDefaultValues()
    {
        $this->assertSame(['abc'], $this->object->one);
        $this->assertSame('bar', $this->object->foo);
        $this->assertSame(123.4, $this->object->number);
        $this->assertEquals(new \DateTime('2000-01-01'), $this->object->dateTime);
    }
}