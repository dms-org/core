<?php

namespace Dms\Core\Tests\Model\Object\Fixtures;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\MutableValueObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 * @immutable
 */
class TestMutableValueObject extends MutableValueObject
{
    /**
     * @var mixed
     */
    public $one;

    /**
     * @var mixed
     */
    public $two;

    /**
     * @var mixed
     */
    public $three;

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->one)->asMixed();

        $class->property($this->two)->asMixed();

        $class->property($this->three)->asMixed();
    }
}