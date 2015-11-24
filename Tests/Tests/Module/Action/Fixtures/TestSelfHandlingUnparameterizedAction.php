<?php

namespace Iddigital\Cms\Core\Tests\Module\Action\Fixtures;

use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Auth\Permission;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Module\Action\SelfHandlingUnparameterizedAction;
use Iddigital\Cms\Core\Tests\Module\Fixtures\TestDto;

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
    protected function name()
    {
        return 'test-unparameterized-action';
    }

    /**
     * Gets the required permissions.
     *
     * @return IPermission[]
     */
    protected function permissions()
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