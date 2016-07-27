<?php

namespace Dms\Core\Tests\Module\Action;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IAuthSystemInPackageContext;
use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\Permission;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ActionTest extends CmsTestCase
{
    /**
     * @param string[] $names
     *
     * @return Permission[]
     */
    protected function mockPermissions(array $names)
    {
        $permissions = [];

        foreach ($names as $name) {
            $permissions[] = Permission::named($name);
        }

        return $permissions;
    }

    /**
     * @param IPermission[] $permissions
     *
     * @return IAuthSystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockAuthWithExpectedVerifyCall(array $permissions)
    {
        $auth = $this->mockAuth();

        $indexedPermissions = [];
        foreach ($permissions as $permission) {
            $indexedPermissions[$permission->getName()] = $permission;
        }

        $auth->expects($this->once())
            ->method('verifyAuthorized')
            ->with($indexedPermissions);

        return $auth;
    }

    /**
     * @return IAuthSystemInPackageContext|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockAuth()
    {
        return $this->getMockForAbstractClass(IAuthSystemInPackageContext::class);
    }
}