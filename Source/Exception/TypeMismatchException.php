<?php

namespace Iddigital\Cms\Core\Exception;

/**
 * Exception for a type mismatch.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class TypeMismatchException extends BaseException
{
    /**
     * @param string $method
     * @param string $argumentName
     * @param string $expectedType
     * @param mixed  $argument
     *
     * @return TypeMismatchException
     */
    public static function argument($method, $argumentName, $expectedType, $argument)
    {
        $actualType = self::getType($argument);

        return new self(
                "Invalid call to {$method}: expecting {$argumentName} to be of type {$expectedType}, {$actualType} given"
        );
    }
}
