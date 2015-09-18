<?php

namespace Iddigital\Cms\Core\Tests\Model\Object\Fixtures;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\TypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class TestAbstractTypedObject extends TypedObject
{
    /**
     * @var string
     */
    public $foo = 'bar';

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->foo)->asString();
    }

    abstract protected function testFoo(array &$foo = [1, 2, 3]);
}