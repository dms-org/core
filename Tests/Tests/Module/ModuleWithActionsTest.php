<?php

namespace Iddigital\Cms\Core\Tests\Module;

use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Auth\Permission;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Module\IAction;
use Iddigital\Cms\Core\Module\IParameterizedAction;
use Iddigital\Cms\Core\Module\IUnparameterizedAction;
use Iddigital\Cms\Core\Module\Module;
use Iddigital\Cms\Core\Tests\Module\Fixtures\ModuleWithActions;
use Iddigital\Cms\Core\Tests\Module\Fixtures\TestDto;
use Iddigital\Cms\Core\Tests\Module\Mock\MockAuthSystem;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ModuleWithActionsTest extends ModuleTestBase
{

    /**
     * @return Module
     */
    protected function buildModule()
    {
        return new ModuleWithActions(new MockAuthSystem());
    }

    /**
     * @return IPermission[]
     */
    protected function expectedPermissions()
    {
        return [
                Permission::named('permission.name'),
                Permission::named('permission.one'),
                Permission::named('permission.two'),
        ];
    }

    /**
     * @return string
     */
    protected function expectedName()
    {
        return 'test-module-with-actions';
    }

    public function testActionGetters()
    {
        $this->assertCount(5, $this->module->getActions());
        $this->assertContainsOnlyInstancesOf(IAction::class, $this->module->getParameterizedActions());

        $this->assertCount(3, $this->module->getParameterizedActions());
        $this->assertContainsOnlyInstancesOf(IParameterizedAction::class, $this->module->getParameterizedActions());

        $this->assertCount(2, $this->module->getUnparameterizedActions());
        $this->assertContainsOnlyInstancesOf(IUnparameterizedAction::class, $this->module->getUnparameterizedActions());


        $this->assertSame(true, $this->module->hasAction('unparameterized-action-no-return'));
        $this->assertSame(true, $this->module->hasUnparameterizedAction('unparameterized-action-no-return'));
        $this->assertSame(false, $this->module->hasParameterizedAction('unparameterized-action-no-return'));

        $this->assertInstanceOf(IUnparameterizedAction::class, $this->module->getAction('unparameterized-action-no-return'));
        $this->assertInstanceOf(IUnparameterizedAction::class, $this->module->getUnparameterizedAction('unparameterized-action-no-return'));

        $this->assertThrows(function () {
            $this->module->getParameterizedAction('unparameterized-action-no-return');
        }, InvalidArgumentException::class);


        $this->assertSame(true, $this->module->hasAction('mapped-form-action'));
        $this->assertSame(true, $this->module->hasParameterizedAction('mapped-form-action'));
        $this->assertSame(false, $this->module->hasUnparameterizedAction('mapped-form-action'));

        $this->assertInstanceOf(IParameterizedAction::class, $this->module->getAction('mapped-form-action'));
        $this->assertInstanceOf(IParameterizedAction::class, $this->module->getParameterizedAction('mapped-form-action'));

        $this->assertThrows(function () {
            $this->module->getUnparameterizedAction('mapped-form-action');
        }, InvalidArgumentException::class);


        $this->assertThrows(function () {
            $this->module->getAction('non-existent-action');
        }, InvalidArgumentException::class);
    }

    public function testUnparameterizedActionWithNoReturn()
    {
        $action = $this->module->getUnparameterizedAction('unparameterized-action-no-return');

        $this->assertInstanceOf(IUnparameterizedAction::class, $action);
        $this->assertSame('unparameterized-action-no-return', $action->getName());
        $this->assertEquals([Permission::named('permission.name')], $action->getRequiredPermissions());
        $this->assertEquals(null, $action->getReturnDtoType());
        $this->assertEquals(false, $action->hasReturnDtoType());
        $this->assertSame(null, $action->run());
    }

    public function testUnparameterizedActionWithReturn()
    {
        $action = $this->module->getUnparameterizedAction('unparameterized-action-with-return');

        $this->assertInstanceOf(IUnparameterizedAction::class, $action);
        $this->assertSame('unparameterized-action-with-return', $action->getName());
        $this->assertEquals([Permission::named('permission.name')], $action->getRequiredPermissions());
        $this->assertEquals(TestDto::class, $action->getReturnDtoType());
        $this->assertEquals(true, $action->hasReturnDtoType());
        $this->assertEquals(new TestDto(), $action->run());
    }

    public function testParameterizedActionWithCustomFormMapping()
    {
        $action = $this->module->getParameterizedAction('mapped-form-action');

        $this->assertInstanceOf(IParameterizedAction::class, $action);
        $this->assertSame('mapped-form-action', $action->getName());
        $this->assertEquals([Permission::named('permission.one')], $action->getRequiredPermissions());
        $this->assertEquals(TestDto::class, $action->getReturnDtoType());
        $this->assertEquals(true, $action->hasReturnDtoType());
        $this->assertEquals(new TestDto('input-handled'), $action->run(['data' => 'input']));
    }

    public function testParameterizedActionWithFormObject()
    {
        $action = $this->module->getParameterizedAction('form-object-action');

        $this->assertInstanceOf(IParameterizedAction::class, $action);
        $this->assertSame('form-object-action', $action->getName());
        $this->assertEquals([Permission::named('permission.one'), Permission::named('permission.two')], $action->getRequiredPermissions());
        $this->assertEquals(TestDto::class, $action->getReturnDtoType());
        $this->assertEquals(true, $action->hasReturnDtoType());
        $this->assertEquals(new TestDto('input-handled-object'), $action->run(['data' => 'input']));
    }

    public function testParameterizedActionWithStagedFormObject()
    {
        $action = $this->module->getParameterizedAction('staged-form-object-action');

        $this->assertInstanceOf(IParameterizedAction::class, $action);
        $this->assertSame('staged-form-object-action', $action->getName());
        $this->assertEquals([], $action->getRequiredPermissions());
        $this->assertEquals(TestDto::class, $action->getReturnDtoType());
        $this->assertEquals(true, $action->hasReturnDtoType());
        $this->assertEquals(new TestDto('some-input-handled-staged'), $action->run(['data' => 'some-input']));
    }
}