<?php

namespace Dms\Core\Tests\Model\Object\Fixtures;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\TypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DuplicatePropertyDefinition extends TypedObject
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

        $class->property($this->three)->asMixed();

        $class->property($this->two)->asMixed();

        $class->property($this->one)->asMixed();
    }

    public static function build()
    {
        return self::construct();
    }
}