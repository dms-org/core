<?php

namespace Dms\Core\Tests\Model\Object\Fixtures;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\TypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ConflictingPropertyNameBase extends TypedObject
{
    /**
     * @var bool
     */
    private $prop;

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->prop)->asBool();
    }
}

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ConflictingPropertyName extends ConflictingPropertyNameBase
{
    /**
     * @var string
     */
    private $prop;

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->prop)->asString();
    }
}