<?php declare(strict_types = 1);

namespace Dms\Core\Auth;

/**
 * Exception for an banned user.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class UserBannedException extends UserException
{
    /**
     * @param IUser $user
     *
     * @return static
     */
    public static function defaultMessage(IUser $user)
    {
        return new self($user, sprintf('Could not authenticate user \'%s\': user is banned', $user->getUsername()));
    }
}
