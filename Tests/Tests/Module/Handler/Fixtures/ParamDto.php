<?php

namespace Iddigital\Cms\Core\Tests\Module\Handler\Fixtures;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\DataTransferObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParamDto extends DataTransferObject
{
    /**
     * @var mixed
     */
    private $value;

    public static function from($value)
    {
        $self = self::construct();
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