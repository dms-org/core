<?php

namespace Iddigital\Cms\Core\Tests\Model\Object\Fixtures;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\TypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidPropertyDefinition extends TypedObject
{
    /**
     * @var mixed
     */
    public $one;

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->one)->asMixed();

        $someVariable = null;
        $class->property($someVariable)->asString();
    }

    public static function build()
    {
        return self::construct();
    }
}