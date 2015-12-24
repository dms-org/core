<?php

namespace Dms\Core\Tests\Model\Object;

use Dms\Core\Model\Object\InaccessiblePropertyException;
use Dms\Core\Model\Object\TypedObjectAccessibilityAssertion;
use Dms\Core\Tests\Model\Object\Fixtures\ExtendedPropertyAccessibilities;
use Dms\Core\Tests\Model\Object\Fixtures\PropertyAccessibilities;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TypedObjectAccessibilityTest extends TypedObjectTest
{
    /**
     * @var PropertyAccessibilities
     */
    protected $object;

    /**
     * @return PropertyAccessibilities
     */
    protected function buildObject()
    {
        return PropertyAccessibilities::build();
    }

    public function testPublicProperty()
    {
        $get                  = $this->object->public;
        $this->object->public = true;
        $this->assertTrue(isset($this->object->public));
    }

    public function testProtectedPropertyFromInvalidScope()
    {
        $this->assertThrows(function () {
            $get = $this->object->protected;
        }, InaccessiblePropertyException::class);

        $this->assertThrows(function () {
            $this->object->protected = true;
        }, InaccessiblePropertyException::class);

        $this->assertFalse(isset($this->object->protected));
    }

    public function testProtectedPropertyFromValidScope()
    {
        $object = $this->object;
        $getter = \Closure::bind(function () use ($object) {
            $get = $object->protected;
        }, $this, ExtendedPropertyAccessibilities::class);
        $getter();

        $setter = \Closure::bind(function () use ($object) {
            $object->protected = true;
        }, $this, ExtendedPropertyAccessibilities::class);
        $setter();

        $issetter = \Closure::bind(function () use ($object) {
            $this->assertTrue(isset($object->protected));
        }, $this, ExtendedPropertyAccessibilities::class);
        $issetter();
    }

    public function testPrivatePropertyFromInvalidScope()
    {
        $this->assertThrows(function () {
            $get = $this->object->private;
        }, InaccessiblePropertyException::class);

        $this->assertThrows(function () {
            $this->object->private = true;
        }, InaccessiblePropertyException::class);

        $this->assertFalse(isset($this->object->private));

        $object = $this->object;
        $this->assertThrows(
                \Closure::bind(function () use ($object) {
                    $get = $object->private;
                }, $this, ExtendedPropertyAccessibilities::class),
                InaccessiblePropertyException::class
        );

        $this->assertThrows(
                \Closure::bind(function () use ($object) {
                    $object->private = true;
                }, $this, ExtendedPropertyAccessibilities::class),
                InaccessiblePropertyException::class
        );


        $issetter = \Closure::bind(function () use ($object) {
            $this->assertFalse(isset($object->private));
        }, $this, ExtendedPropertyAccessibilities::class);
        $issetter();
    }

    public function testPrivatePropertyFromValidScope()
    {
        $object = $this->object;
        $getter = \Closure::bind(function () use ($object) {
            $get = $object->private;
        }, $this, PropertyAccessibilities::class);
        $getter();

        $setter = \Closure::bind(function () use ($object) {
            $object->private = true;
        }, $this, PropertyAccessibilities::class);
        $setter();

        $issetter = \Closure::bind(function () use ($object) {
            $this->assertTrue(isset($object->private));
        }, $this, PropertyAccessibilities::class);
        $issetter();
    }

    public function testDisablingPropertyAccessibilityAssertion()
    {
        $this->assertTrue(TypedObjectAccessibilityAssertion::isEnabled());

        TypedObjectAccessibilityAssertion::enable(false);

        $this->assertFalse(TypedObjectAccessibilityAssertion::isEnabled());

        $this->assertNull($this->object->public);
        $this->object->public = true;
        $this->assertTrue($this->object->public);

        $this->assertNull($this->object->protected);
        $this->object->protected = true;
        $this->assertTrue($this->object->protected);

        $this->assertNull($this->object->private);
        $this->object->private = true;
        $this->assertTrue($this->object->private);

        TypedObjectAccessibilityAssertion::enable(true);

        $this->assertTrue(TypedObjectAccessibilityAssertion::isEnabled());

        $this->assertThrows(function () {
                    $this->object->private = true;
                },
                InaccessiblePropertyException::class
        );
    }
}