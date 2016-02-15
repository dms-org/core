<?php declare(strict_types = 1);

namespace Dms\Core\Exception;

use Dms\Core\Model\Type\Builder\Type;

/**
 * Exception for a type mismatch.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class TypeMismatchException extends BaseException
{
    use TypeAsserts;

    /**
     * @param string $method
     * @param string $argumentName
     * @param string $expectedType
     * @param mixed  $argument
     *
     * @return TypeMismatchException
     */
    public static function argument(string $method, string $argumentName, string $expectedType, $argument) : TypeMismatchException
    {
        $actualType = Type::from($argument)->asTypeString();

        return new self(
                "Invalid call to {$method}: expecting \${$argumentName} to be of type {$expectedType}, {$actualType} given"
        );
    }
}
