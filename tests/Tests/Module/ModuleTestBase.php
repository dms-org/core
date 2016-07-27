<?php

namespace Dms\Core\Tests\Module;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Auth\IAdmin;
use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\Permission;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Module\Definition\ModuleDefinition;
use Dms\Core\Module\IAction;
use Dms\Core\Module\Module;
use Dms\Core\Module\ModuleLoadingContext;
use Dms\Core\Table\IDataTable;
use Dms\Core\Tests\Helpers\Mock\MockingIocContainer;
use Dms\Core\Tests\Module\Mock\MockAuthSystem;
use Dms\Core\Tests\Module\Mock\MockEventDispatcher;
use Dms\Core\Tests\Table\DataSource\DataTableHelper;
use Dms\Core\Widget\IWidget;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ModuleTestBase extends CmsTestCase
{
    /**
     * @var MockingIocContainer
     */
    protected $iocContainer;

    /**
     * @var MockEventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var MockAuthSystem
     */
    protected $authSystem;

    /**
     * @var Module
     */
    protected $module;

    public function setUp()
    {
        $this->authSystem      = new MockAuthSystem($this->getMockForAbstractClass(IAdmin::class), $this);
        $this->iocContainer    = $this->authSystem->getIocContainer();
        $this->eventDispatcher = $this->authSystem->getEventDispatcher();

        $this->iocContainer->bindings[ModuleLoadingContext::class] = new ModuleLoadingContext('some-package');

        $this->module = $this->buildModule($this->authSystem);
    }

    /**
     * @param MockAuthSystem $authSystem
     *
     * @return Module
     */
    abstract protected function buildModule(MockAuthSystem $authSystem);

    /**
     * @return IPermission[]
     */
    abstract protected function expectedPermissions();

    /**
     * @return IPermission[]
     */
    abstract protected function expectedRequiredPermissions();

    /**
     * @return string
     */
    abstract protected function expectedName();

    public function testName()
    {
        $this->assertSame($this->expectedName(), $this->module->getName());
    }

    public function testPermissions($extraNamespace = null)
    {
        $expected = $this->expectedPermissions();

        foreach ($expected as $key => $permission) {
            $permission = $permission->inNamespace($this->module->getName());
            if ($extraNamespace) {
                $permission = $permission->inNamespace($extraNamespace);
            }

            $expected[$key] = $permission;
        }

        $actual = array_values($this->module->getPermissions());
        sort($expected, SORT_REGULAR);
        sort($actual, SORT_REGULAR);
        $this->assertEquals($expected, $actual);
    }

    public function testWidgetPermissions()
    {
        $this->module->setPackageName('some-package');

        /** @var IWidget $widget */
        foreach ($this->module->getWidgets() as $widget) {
            $this->authSystem->setIsAuthorized(true);
            $this->assertSame(true, $widget->isAuthorized());
            $this->authSystem->setIsAuthorized(false);
            $this->assertSame(false, $widget->isAuthorized());

            $this->assertSame('some-package', $widget->getPackageName());
            $this->assertSame($this->module->getName(), $widget->getModuleName());

            $this->assertEquals(
                Permission::namespaceAll($this->expectedRequiredPermissions(), 'some-package.' . $this->module->getName()),
                $widget->getRequiredPermissions()
            );
        }
    }

    public function testWithoutRequiredPermissions()
    {
        $module = $this->module->withoutRequiredPermissions();

        $this->assertSame(get_class($this->module), get_class($module));
        $this->assertSame([], $module->getRequiredPermissions());

        foreach ($module->getActions() as $action) {
            $this->assertSame([], $action->getRequiredPermissions());
        }

        foreach ($module->getWidgets() as $widget) {
            $this->assertSame([], $widget->getRequiredPermissions());
        }
    }

    protected function assertDataTableEquals(array $expectedSections, IDataTable $dataTable)
    {
        $this->assertEquals(
            DataTableHelper::normalizeSingleComponents($expectedSections),
            DataTableHelper::covertDataTableToNormalizedArray($dataTable)
        );
    }

    public function testPackageName()
    {
        $this->assertSame(null, $this->module->getPackageName());

        $this->module->setPackageName('some-package');

        $this->assertSame('some-package', $this->module->getPackageName());
        $this->testPermissions('some-package');

        $this->assertThrows(function () {
            $this->module->setPackageName('abc');
        }, InvalidOperationException::class);

        /** @var IAction $action */
        foreach ($this->module->getActions() as $action) {
            $this->assertSame('some-package', $action->getPackageName());
        }
    }

    public function testIsAuthorized()
    {
        $this->authSystem->setIsAuthorized(true);
        $this->assertSame(true, $this->module->isAuthorized());

        $this->authSystem->setIsAuthorized(false);
        $this->assertSame(false, $this->module->isAuthorized());
    }

    public function testDefineEventsAreEmitted()
    {
        $events = [];

        foreach ($this->eventDispatcher->getEmittedEvents() as $event) {
            $events[] = array_map(function ($i) {
                return is_scalar($i) ? $i : get_class($i);
            }, $event);
        }

        $this->assertEquals($this->expectedDefineEvents(), $events);
    }

    protected function expectedDefineEvents()
    {
        return [
            ['some-package.' . $this->module->getName() . '.define', ModuleDefinition::class],
            ['some-package.' . $this->module->getName() . '.defined', ModuleDefinition::class],
        ];
    }
}