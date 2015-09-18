<?php

namespace Iddigital\Cms\Core\Tests\Model\Object;

use Iddigital\Cms\Core\Model\Object\InaccessiblePropertyException;
use Iddigital\Cms\Core\Tests\Model\Object\Fixtures\ExtendedPropertyAccessibilities;
use Iddigital\Cms\Core\Tests\Model\Object\Fixtures\PropertyAccessibilities;

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
}