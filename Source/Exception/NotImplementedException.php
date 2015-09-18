<?php

namespace Iddigital\Cms\Core\Exception;

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
     * @return InvalidOperationException
     */
    public static function method($method)
    {
        return self::format('Invalid call to %s: not implemented', $method);
    }
}
