<?php declare(strict_types = 1);

namespace Dms\Core\Auth;

/**
 * Exception for an banned admin.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class AdminBannedException extends AdminException
{
    /**
     * @param IAdmin $admin
     *
     * @return static
     */
    public static function defaultMessage(IAdmin $admin)
    {
        return new self($admin, sprintf('Could not authenticate admin \'%s\': the account is banned', $admin->getUsername()));
    }
}
