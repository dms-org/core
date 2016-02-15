<?php declare(strict_types = 1);

namespace Dms\Core\Model\Object;

use Dms\Core\Exception;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;

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