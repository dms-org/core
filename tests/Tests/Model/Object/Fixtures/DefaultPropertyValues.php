<?php

namespace Dms\Core\Tests\Model\Object\Fixtures;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\TypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DefaultPropertyValues extends TypedObject
{
    /**
     * @var mixed
     */
    public $one = ['abc'];

    /**
     * @var string
     */
    public $foo = 'bar';

    /**
     * @var float
     */
    public $number = 123.4;

    /**
     * @var \DateTime
     */
    public $dateTime;

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->one)->asMixed();

        $class->property($this->foo)->asString();

        $class->property($this->number)->asFloat();

        $class->property($this->dateTime)->asObject(\DateTime::class);

        $this->dateTime = new \DateTime('2000-01-01');
    }

    public static function build()
    {
        return new self();
    }
}