<?php

namespace Dms\Core\Tests\Module\Handler\Fixtures;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\DataTransferObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParamDto extends DataTransferObject
{
    /**
     * @var mixed
     */
    public $value;

    public static function from($value)
    {
        $self = static::construct();
        $self->value = $value;

        return $self;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->value)->asMixed();
    }
}