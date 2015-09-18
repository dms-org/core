<?php

namespace Iddigital\Cms\Core\Tests\Module\Action;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Auth\IPermission;

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

        $auth->expects($this->once())
                ->method('verifyAuthorized')
                ->with($permissions);

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