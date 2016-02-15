<?php declare(strict_types = 1);

namespace Dms\Core\Model\Object;

use Dms\Core\Exception;

/**
 * Exception for an invalid property value.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ImmutablePropertyException extends Exception\BaseException
{
    public function __construct($class, $property)
    {
        parent::__construct(
                "Cannot set property {$class}::\${$property}: property is immutable and the value cannot be changed"
        );
    }

}