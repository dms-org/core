<?php

namespace Dms\Core\Tests\Module;

use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\Permission;
use Dms\Core\Module\ActionNotFoundException;
use Dms\Core\Module\IAction;
use Dms\Core\Module\IParameterizedAction;
use Dms\Core\Module\IUnparameterizedAction;
use Dms\Core\Module\Module;
use Dms\Core\Tests\Module\Fixtures\ModuleWithActions;
use Dms\Core\Tests\Module\Fixtures\TestDto;
use Dms\Core\Tests\Module\Mock\MockAuthSystem;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ModuleWithActionsTest extends ModuleTestBase
{

    /**
     * @param MockAuthSystem $authSystem
     *
     * @return Module
     */
    protected function buildModule(MockAuthSystem $authSystem)
    {
        return new ModuleWithActions($authSystem);
    }

    /**
     * @return IPermission[]
     */
    protected function expectedPermissions()
    {
        return [
            Permission::named('some-permission'),
            Permission::named('permission.name'),
            Permission::named('permission.one'),
            Permission::named('permission.two'),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function expectedRequiredPermissions()
    {
        return [
            Permission::named('some-permission'),
        ];
    }

    /**
     * @return string
     */
    protected function expectedName()
    {
        return 'test-module-with-actions';
    }

    /**
     * @return void
     * @throws ActionNotFoundException
     * @throws \Exception
     */
    public function testActionGetters()
    {
        $this->assertCount(7, $this->module->getActions());
        $this->assertContainsOnlyInstancesOf(IAction::class, $this->module->getParameterizedActions());

        $this->assertCount(4, $this->module->getParameterizedActions());
        $this->assertContainsOnlyInstancesOf(IParameterizedAction::class, $this->module->getParameterizedActions());

        $this->assertCount(3, $this->module->getUnparameterizedActions());
        $this->assertContainsOnlyInstancesOf(IUnparameterizedAction::class, $this->module->getUnparameterizedActions());


        $this->assertSame(true, $this->module->hasAction('unparameterized-action-no-return'));
        $this->assertSame(true, $this->module->hasUnparameterizedAction('unparameterized-action-no-return'));
        $this->assertSame(false, $this->module->hasParameterizedAction('unparameterized-action-no-return'));

        $this->assertInstanceOf(IUnparameterizedAction::class, $this->module->getAction('unparameterized-action-no-return'));
        $this->assertInstanceOf(IUnparameterizedAction::class, $this->module->getUnparameterizedAction('unparameterized-action-no-return'));

        $this->assertThrows(function () {
            $this->module->getParameterizedAction('unparameterized-action-no-return');
        }, ActionNotFoundException::class);


        $this->assertSame(true, $this->module->hasAction('mapped-form-action'));
        $this->assertSame(true, $this->module->hasParameterizedAction('mapped-form-action'));
        $this->assertSame(false, $this->module->hasUnparameterizedAction('mapped-form-action'));

        $this->assertInstanceOf(IParameterizedAction::class, $this->module->getAction('mapped-form-action'));
        $this->assertInstanceOf(IParameterizedAction::class, $this->module->getParameterizedAction('mapped-form-action'));

        $this->assertThrows(function () {
            $this->module->getUnparameterizedAction('mapped-form-action');
        }, ActionNotFoundException::class);


        $this->assertThrows(function () {
            $this->module->getAction('non-existent-action');
        }, ActionNotFoundException::class);
    }

    public function testUnparameterizedActionWithNoReturn()
    {
        $action = $this->module->getUnparameterizedAction('unparameterized-action-no-return');

        $this->assertInstanceOf(IUnparameterizedAction::class, $action);
        $this->assertSame([], $action->getMetadata());
        $this->assertSame('unparameterized-action-no-return', $action->getName());
        $this->assertEquals(
            [
                Permission::named('some-package.test-module-with-actions.some-permission'),
                Permission::named('some-package.test-module-with-actions.permission.name')
            ],
            array_values($action->getRequiredPermissions())
        );
        $this->assertEquals(null, $action->getReturnTypeClass());
        $this->assertEquals(false, $action->hasReturnType());
        $this->assertSame(null, $action->run());
    }

    public function testUnparameterizedActionWithReturn()
    {
        $action = $this->module->getUnparameterizedAction('unparameterized-action-with-return');

        $this->assertInstanceOf(IUnparameterizedAction::class, $action);
        $this->assertSame('unparameterized-action-with-return', $action->getName());
        $this->assertEquals(
            [
                Permission::named('some-package.test-module-with-actions.some-permission'),
                Permission::named('some-package.test-module-with-actions.permission.name')
            ],
            array_values($action->getRequiredPermissions())
        );
        $this->assertEquals(TestDto::class, $action->getReturnTypeClass());
        $this->assertEquals(true, $action->hasReturnType());
        $this->assertEquals(new TestDto(), $action->run());
    }

    public function testParameterizedActionWithCustomFormMapping()
    {
        $action = $this->module->getParameterizedAction('mapped-form-action');

        $this->assertInstanceOf(IParameterizedAction::class, $action);
        $this->assertSame('mapped-form-action', $action->getName());
        $this->assertEquals(
            [
                Permission::named('some-package.test-module-with-actions.some-permission'),
                Permission::named('some-package.test-module-with-actions.permission.one')
            ],
            array_values($action->getRequiredPermissions())
        );
        $this->assertEquals(TestDto::class, $action->getReturnTypeClass());
        $this->assertEquals(true, $action->hasReturnType());
        $this->assertEquals(new TestDto('input-handled'), $action->run(['data' => 'input']));
    }

    public function testParameterizedActionWithDefaultFormMapping()
    {
        $action = $this->module->getParameterizedAction('array-form-action');

        $this->assertInstanceOf(IParameterizedAction::class, $action);
        $this->assertSame('array-form-action', $action->getName());
        $this->assertEquals(
            [
                Permission::named('some-package.test-module-with-actions.some-permission'),
                Permission::named('some-package.test-module-with-actions.permission.one')
            ],
            array_values($action->getRequiredPermissions())
        );
        $this->assertEquals(TestDto::class, $action->getReturnTypeClass());
        $this->assertEquals(true, $action->hasReturnType());
        $this->assertEquals(new TestDto('input-handled'), $action->run(['data' => 'input']));
    }

    public function testParameterizedActionWithFormObject()
    {
        $action = $this->module->getParameterizedAction('form-object-action');

        $this->assertInstanceOf(IParameterizedAction::class, $action);
        $this->assertSame('form-object-action', $action->getName());
        $this->assertEquals([
            Permission::named('some-package.test-module-with-actions.some-permission'),
            Permission::named('some-package.test-module-with-actions.permission.one'),
            Permission::named('some-package.test-module-with-actions.permission.two'),
        ], array_values($action->getRequiredPermissions()));
        $this->assertEquals(TestDto::class, $action->getReturnTypeClass());
        $this->assertEquals(true, $action->hasReturnType());
        $this->assertEquals(new TestDto('input-handled-object'), $action->run(['data' => 'input']));
    }

    public function testParameterizedActionWithStagedFormObject()
    {
        $action = $this->module->getParameterizedAction('staged-form-object-action');

        $this->assertInstanceOf(IParameterizedAction::class, $action);
        $this->assertSame('staged-form-object-action', $action->getName());
        $this->assertEquals(
            [Permission::named('some-package.test-module-with-actions.some-permission')],
            array_values($action->getRequiredPermissions())
        );
        $this->assertEquals(TestDto::class, $action->getReturnTypeClass());
        $this->assertEquals(true, $action->hasReturnType());
        $this->assertEquals(new TestDto('some-input-handled-staged'), $action->run(['data' => 'some-input']));
    }

    public function testActioNWithMetadata()
    {
        $action = $this->module->getAction('action-with-metadata');

        $this->assertSame(['some' => 'metadata'], $action->getMetadata());
    }
}