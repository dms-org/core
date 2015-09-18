<?php

namespace Iddigital\Cms\Core\Tests\Model\Object\Fixtures;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\TypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NullableProperty extends TypedObject
{
    /**
     * @var int|null
     */
    public $prop;

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->prop)->nullable()->asInt();
    }
}