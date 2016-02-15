<?php

namespace Dms\Core\Tests\Module\Action\Fixtures;

use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\Permission;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Module\Action\SelfHandlingUnparameterizedAction;
use Dms\Core\Tests\Module\Fixtures\TestDto;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestSelfHandlingUnparameterizedAction extends SelfHandlingUnparameterizedAction
{
    /**
     * Gets the action name.
     *
     * @return string
     */
    protected function name() : string
    {
        return 'test-unparameterized-action';
    }

    /**
     * Gets the required permissions.
     *
     * @return IPermission[]
     */
    protected function permissions() : array
    {
        return [
                Permission::named('test-permission')
        ];
    }

    /**
     * Gets the return dto type.
     *
     * @return string|null
     */
    protected function returnType()
    {
        return TestDto::class;
    }

    /**
     * Runs the action handler.
     *
     * @returns TestDto
     * @throws TypeMismatchException
     */
    public function runHandler()
    {
        return new TestDto('123');
    }

}