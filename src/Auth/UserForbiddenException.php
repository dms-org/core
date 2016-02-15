<?php declare(strict_types = 1);

namespace Dms\Core\Auth;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Util\Debug;

/**
 * Exception for an action that is invalid with the current
 * authenticated user.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class UserForbiddenException extends UserException
{
    /**
     * @var IPermission[]
     */
    private $requiredPermissions = [];

    /**
     * @param IUser         $user
     * @param IPermission[] $requiredPermissions
     */
    public function __construct(IUser $user, array $requiredPermissions)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'requiredPermissions', $requiredPermissions,
                IPermission::class);

        $permissionNames = [];
        foreach ($requiredPermissions as $permission) {
            $permissionNames[] = $permission->getName();
        }

        parent::__construct(
                $user,
                'The currently authenticated user is forbidden from performing the requested action, required permissions: ' . Debug::formatValues($permissionNames)
        );

        $this->requiredPermissions = $requiredPermissions;
    }

    /**
     * @return IPermission[]
     */
    public function getRequiredPermissions() : array
    {
        return $this->requiredPermissions;
    }
}
