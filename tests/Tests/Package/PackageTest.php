<?php

namespace Dms\Core\Tests\Package;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Auth\Permission;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Tests\Helpers\Mock\MockingIocContainer;
use Dms\Core\Tests\Module\Fixtures\ModuleWithActions;
use Dms\Core\Tests\Module\Fixtures\ModuleWithCharts;
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

    public function setUp()
    {
        $this->package = new TestPackage(new MockingIocContainer($this));
    }

    public function testNew()
    {
        $this->assertInstanceOf(MockingIocContainer::class, $this->package->getIocContainer());
    }

    public function testName()
    {
        $this->assertSame('test-package', $this->package->getName());
    }

    public function testModuleNames()
    {
        $this->assertSame(['test-module-with-actions', 'test-module-with-charts'], $this->package->getModuleNames());
    }

    public function testHasModules()
    {
        $this->assertSame(true, $this->package->hasModule('test-module-with-actions'));
        $this->assertSame(true, $this->package->hasModule('test-module-with-charts'));
        $this->assertSame(false, $this->package->hasModule('non-existent'));
    }

    public function testLoadModules()
    {
        $modules = $this->package->loadModules();

        $this->assertSame(['test-module-with-actions', 'test-module-with-charts'], array_keys($modules));

        $this->assertInstanceOf(ModuleWithActions::class, $modules['test-module-with-actions']);
        $this->assertInstanceOf(ModuleWithCharts::class, $modules['test-module-with-charts']);
    }

    public function testLoadModule()
    {
        $this->assertInstanceOf(ModuleWithActions::class, $module1 = $this->package->loadModule('test-module-with-actions'));
        $this->assertInstanceOf(ModuleWithCharts::class, $module2 = $this->package->loadModule('test-module-with-charts'));

        // Should cache modules
        $this->assertSame($module1, $this->package->loadModule('test-module-with-actions'));
        $this->assertSame($module2, $this->package->loadModule('test-module-with-charts'));

        $this->assertThrows(function () {
            $this->package->loadModule('non-existent');
        }, InvalidArgumentException::class);
    }

    public function testLoadPermissionsInModuleNamespace()
    {
        /**
         * @see ModuleWithActions Defined permissions in module
         */
        $this->assertEquals([
                Permission::named('test-package.test-module-with-actions.permission.name'),
                Permission::named('test-package.test-module-with-actions.permission.one'),
                Permission::named('test-package.test-module-with-actions.permission.two'),
        ], $this->package->loadPermissions());
    }
}