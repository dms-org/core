<?php

namespace Iddigital\Cms\Core\Tests\Model\Object\Fixtures;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\TypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ImmutableProperty extends TypedObject
{
    /**
     * @var mixed
     */
    public $one;

    /**
     * @var mixed
     * @immutable
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

        $class->property($this->two)->immutable()->asMixed();

        $class->property($this->three)->asMixed();
    }
}