<?php

namespace Iddigital\Cms\Core\Tests\Model\Object;

use Iddigital\Cms\Core\Model\Object\InvalidPropertyValueException;
use Iddigital\Cms\Core\Model\Object\TypedObject;
use Iddigital\Cms\Core\Model\Object\UndefinedPropertyException;
use Iddigital\Cms\Core\Tests\Model\Object\Fixtures\Person;

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
        $this->setExpectedException(UndefinedPropertyException::class);
        $this->object->blah;
    }

    public function testPropertyUpdatingWithInvalidValue()
    {
        $this->setExpectedException(InvalidPropertyValueException::class);
        $this->object->firstName = null;
    }

    public function testObjectPropertyUpdatingWithInvalidValue()
    {
        $this->setExpectedException(InvalidPropertyValueException::class);
        $this->object->dateOfBirth = new \stdClass();
    }
}