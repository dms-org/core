<?php

namespace Dms\Core\Tests\Package;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Auth\IAdmin;
use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\Permission;
use Dms\Core\Event\IEventDispatcher;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Module\Module;
use Dms\Core\Module\ModuleNotFoundException;
use Dms\Core\Package\Definition\PackageDefinition;
use Dms\Core\Tests\Helpers\Mock\MockingIocContainer;
use Dms\Core\Tests\Module\Fixtures\ModuleWithActions;
use Dms\Core\Tests\Module\Fixtures\ModuleWithCharts;
use Dms\Core\Tests\Module\Mock\MockAuthSystem;
use Dms\Core\Tests\Module\Mock\MockEventDispatcher;
use Dms\Core\Tests\Package\Fixtures\TestPackage;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PackageTest extends CmsTestCase
{
    /**
     * @var TestPackage
     */
    protected $package;

    /**
     * @var MockEventDispatcher
     */
    protected $eventDispatcher;

    public function setUp()
    {
        $iocContainer  = new MockingIocContainer($this);

        $iocContainer->bindValue(
            IAuthSystem::class,
            new MockAuthSystem($this->getMockForAbstractClass(IAdmin::class), $this, '')
        );

        $iocContainer->bindValue(
            IEventDispatcher::class,
            $this->eventDispatcher = new MockEventDispatcher()
        );

        $this->package = new TestPackage($iocContainer);

    }

    public function testNew()
    {
        $this->assertInstanceOf(MockingIocContainer::class, $this->package->getIocContainer());
    }

    public function testName()
    {
        $this->assertSame('test-package', $this->package->getName());
    }

    public function testMetadata()
    {
        $this->assertSame([
            'another-key' => 'some-more-metadata', 'key' => 'some-metadata'
        ], $this->package->getAllMetadata());

        $this->assertSame(true, $this->package->hasMetadata('key'));
        $this->assertSame(false, $this->package->hasMetadata('unknown'));

        $this->assertSame('some-metadata', $this->package->getMetadata('key'));
        $this->assertSame(null, $this->package->getMetadata('unknown'));
    }

    public function testModuleNames()
    {
        $this->assertSame(['test-module-with-actions', 'test-module-with-charts', 'test-module-factory'], $this->package->getModuleNames());
    }

    public function testDashboard()
    {
        $this->assertSame(false, $this->package->hasDashboard());

        $this->assertThrows(function () {
            $this->package->loadDashboard();
        }, InvalidOperationException::class);
    }

    public function testHasModules()
    {
        $this->assertSame(true, $this->package->hasModule('test-module-with-actions'));
        $this->assertSame(true, $this->package->hasModule('test-module-with-charts'));
        $this->assertSame(true, $this->package->hasModule('test-module-factory'));
        $this->assertSame(false, $this->package->hasModule('non-existent'));
    }

    public function testLoadModules()
    {
        $modules = $this->package->loadModules();

        $this->assertSame(['test-module-with-actions', 'test-module-with-charts', 'test-module-factory'], array_keys($modules));

        $this->assertInstanceOf(ModuleWithActions::class, $modules['test-module-with-actions']);
        $this->assertInstanceOf(ModuleWithCharts::class, $modules['test-module-with-charts']);
        $this->assertInstanceOf(Module::class, $modules['test-module-factory']);
    }

    public function testLoadModule()
    {
        $this->assertInstanceOf(ModuleWithActions::class, $module1 = $this->package->loadModule('test-module-with-actions'));
        $this->assertInstanceOf(ModuleWithCharts::class, $module2 = $this->package->loadModule('test-module-with-charts'));
        $this->assertInstanceOf(Module::class, $module3 = $this->package->loadModule('test-module-factory'));

        // Should cache modules
        $this->assertSame($module1, $this->package->loadModule('test-module-with-actions'));
        $this->assertSame($module2, $this->package->loadModule('test-module-with-charts'));
        $this->assertSame($module3, $this->package->loadModule('test-module-factory'));

        $this->assertThrows(function () {
            $this->package->loadModule('non-existent');
        }, ModuleNotFoundException::class);
    }

    public function testLoadPermissionsInPackageNamespace()
    {
        /**
         * @see ModuleWithActions Defined permissions in module
         */
        $this->assertEquals([
                Permission::named('test-package.test-module-with-actions.some-permission'),
                Permission::named('test-package.test-module-with-actions.permission.name'),
                Permission::named('test-package.test-module-with-actions.permission.one'),
                Permission::named('test-package.test-module-with-actions.permission.two'),
        ], $this->package->loadPermissions());
    }

    public function testDefineEventsAreEmitted()
    {
        $events = [];

        foreach ($this->eventDispatcher->getEmittedEvents() as $event) {
            $events[] = array_map(function ($i) {
                return is_scalar($i) ? $i : get_class($i);
            }, $event);
        }

        $this->assertEquals([
            ['test-package.define', PackageDefinition::class],
            ['test-package.defined', PackageDefinition::class],
        ], $events);
    }
}