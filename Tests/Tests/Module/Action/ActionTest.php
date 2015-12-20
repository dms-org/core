<?php

namespace Dms\Core\Tests\Module\Action;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IPermission;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ActionTest extends CmsTestCase
{
    /**
     * @param string[] $names
     *
     * @return IPermission[]
     */
    protected function mockPermissions(array $names)
    {
        $permissions = [];

        foreach ($names as $name) {
            $permission = $this->getMockForAbstractClass(IPermission::class);
            $permission->method('getName')->willReturn($name);

            $permissions[] = $permission;
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
     * @return IAuthSystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockAuth()
    {
        return $this->getMockForAbstractClass(IAuthSystem::class);
    }
}