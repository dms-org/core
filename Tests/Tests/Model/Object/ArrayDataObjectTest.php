<?php

namespace Iddigital\Cms\Core\Tests\Model\Object;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\Object\ArrayDataObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayDataObjectTest extends TypedObjectTest
{
    /**
     * @var ArrayDataObject
     */
    protected $object;

    /**
     * @return ArrayDataObject
     */
    protected function buildObject()
    {
        return new ArrayDataObject([
                1      => 'abc',
                'foo'  => 'bar',
                'null' => null,
        ]);
    }

    public function testGetters()
    {
        $this->assertSame([
                1      => 'abc',
                'foo'  => 'bar',
                'null' => null,
        ], $this->object->getArray());
    }

    public function testArrayAccessGetters()
    {
        $this->assertTrue(isset($this->object[1]));
        $this->assertTrue(isset($this->object['foo']));

        $this->assertFalse(isset($this->object[2]));
        $this->assertFalse(isset($this->object['abc']));
        $this->assertFalse(isset($this->object['null']));

        $this->assertSame('abc', $this->object[1]);
        $this->assertSame('bar', $this->object['foo']);
        $this->assertSame(null, $this->object['null']);

        $this->assertThrows(function () {
            $this->object['non-existent'];
        }, InvalidArgumentException::class);
    }

    public function testWith()
    {
        $newData = $this->object->with([
                2     => 'def',
                3     => 123,
                'foo' => 'baz',
        ]);

        $this->assertNotEquals($this->object, $newData);

        $this->assertSame([
                1      => 'abc',
                'foo'  => 'bar',
                'null' => null,
        ], $this->object->getArray());

        $this->assertSame([
                2     => 'def',
                3     => 123,
                'foo' => 'baz',
                1     => 'abc',
                'null' => null,
        ], $newData->getArray());
    }

    public function testWithout()
    {
        $newData = $this->object->without([1, 'null', 'non-existent']);

        $this->assertNotEquals($this->object, $newData);

        $this->assertSame([
                1      => 'abc',
                'foo'  => 'bar',
                'null' => null,
        ], $this->object->getArray());

        $this->assertSame([
                'foo'  => 'bar',
        ], $newData->getArray());
    }

    public function testJsonSerializing()
    {
        $this->assertSame($this->object->getArray(), $this->object->jsonSerialize());

        $this->assertSame(json_encode($this->object->getArray()), json_encode($this->object));
    }
}