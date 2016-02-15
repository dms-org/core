<?php declare(strict_types = 1);

namespace Dms\Core\Auth;

use Dms\Core\Exception\BaseException;

/**
 * Exception for invalid login credentials.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class InvalidCredentialsException extends BaseException
{
    /**
     * @param string $username
     *
     * @return static
     */
    public static function defaultMessage(string $username)
    {
        return static::format('Could not authenticate user \'%s\': credentials are invalid', $username);
    }
}
