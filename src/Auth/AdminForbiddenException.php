<?php declare(strict_types = 1);

namespace Dms\Core\Auth;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Util\Debug;

/**
 * Exception for an action that is invalid with the current
 * authenticated admin.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class AdminForbiddenException extends AdminException
{
    /**
     * @var IPermission[]
     */
    private $requiredPermissions = [];

    /**
     * @param IAdmin        $admin
     * @param IPermission[] $requiredPermissions
     */
    public function __construct(IAdmin $admin, array $requiredPermissions)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'requiredPermissions', $requiredPermissions,
                IPermission::class);

        $permissionNames = [];
        foreach ($requiredPermissions as $permission) {
            $permissionNames[] = $permission->getName();
        }

        parent::__construct(
                $admin,
                'The currently authenticated admin is forbidden from performing the requested action, required permissions: ' . Debug::formatValues($permissionNames)
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
