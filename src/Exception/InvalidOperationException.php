<?php declare(strict_types = 1);

namespace Dms\Core\Exception;

/**
 * Exception for invalid operation.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class InvalidOperationException extends BaseException
{
    /**
     * Constructs a new exception for a bad method call.
     *
     * @param string $method
     * @param string $message
     * @param  string ...
     *
     * @return InvalidOperationException
     */
    public static function methodCall(string $method, string $message, $_ = null) : InvalidOperationException
    {
        return self::formatArray('Invalid call to %s: ' . $message, array_merge([$method], array_slice(func_get_args(), 2)));
    }
}
