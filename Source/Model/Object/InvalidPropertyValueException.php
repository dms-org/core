<?php

namespace Iddigital\Cms\Core\Model\Object;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\IType;

/**
 * Exception for an invalid property value.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidPropertyValueException extends Exception\BaseException
{
    public function __construct($class, $property, IType $expectedType, $value)
    {
        $type = Type::from($value)->asTypeString();
        parent::__construct(
                "Cannot set property {$class}::\${$property}: expecting {$expectedType->asTypeString()}, {$type} given"
        );
    }

}