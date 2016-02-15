<?php declare(strict_types = 1);

namespace Dms\Core\Exception;

/**
 * Exception for unimplemented method.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class NotImplementedException extends BaseException
{
    /**
     * Constructs a new exception for a bad method call.
     *
     * @param string $method
     *
     * @return self
     */
    public static function method(string $method) : self
    {
        return self::format('Invalid call to %s: not implemented', $method);
    }
}
