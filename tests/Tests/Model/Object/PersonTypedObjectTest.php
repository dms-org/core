<?php

namespace Dms\Core\Tests\Model\Object;

use Dms\Core\Model\Object\InvalidPropertyValueException;
use Dms\Core\Model\Object\TypedObject;
use Dms\Core\Model\Object\UndefinedPropertyException;
use Dms\Core\Tests\Model\Object\Fixtures\Person;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PersonTypedObjectTest extends TypedObjectTest
{
    /**
     * @var Person
     */
    protected $object;

    /**
     * @return TypedObject
     */
    protected function buildObject()
    {
        return new Person('John', 'Smith', new \DateTime('1969-03-15'), true, 'john@gmail.com.au');
    }

    public function testToArray()
    {
        $array = $this->object->toArray();

        $this->assertEquals([
                'firstName'    => 'John',
                'lastName'     => 'Smith',
                'dateOfBirth'  => new \DateTime('1969-03-15'),
                'married'      => true,
                'emailAddress' => 'john@gmail.com.au',
        ], $array);
    }

    public function testToArrayAndRehydrate()
    {
        $this->assertEquals($this->object, Person::hydrateNew($this->object->toArray()));
    }

    public function testPropertyAccessing()
    {
        $this->assertSame('John', $this->object->firstName);
        $this->assertSame('Smith', $this->object->lastName);
        $this->assertEquals(new \DateTime('1969-03-15'), $this->object->dateOfBirth);
        $this->assertTrue($this->object->married);
        $this->assertSame('john@gmail.com.au', $this->object->emailAddress);
    }

    public function testPropertyUpdating()
    {
        $this->object->firstName = 'Barry';

        $this->assertSame('Barry', $this->object->firstName);
        $this->assertSame('Smith', $this->object->lastName);
    }

    public function testGetPropertyWithInvalidName()
    {
        $this->expectException(UndefinedPropertyException::class);
        $this->object->blah;
    }

    public function testPropertyUpdatingWithInvalidValue()
    {
        $this->expectException(InvalidPropertyValueException::class);
        $this->object->firstName = null;
    }

    public function testObjectPropertyUpdatingWithInvalidValue()
    {
        $this->expectException(InvalidPropertyValueException::class);
        $this->object->dateOfBirth = new \stdClass();
    }
}