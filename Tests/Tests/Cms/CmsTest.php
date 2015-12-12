<?php

namespace Iddigital\Cms\Core\Tests\Cms;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Auth\Permission;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Language\ILanguageProvider;
use Iddigital\Cms\Core\Package\PackageNotInstalledException;
use Iddigital\Cms\Core\Tests\Cms\Fixtures\TestCms;
use Iddigital\Cms\Core\Tests\Helpers\Mock\MockingIocContainer;
use Iddigital\Cms\Core\Tests\Package\Fixtures\InvalidModuleClassPackage;
use Iddigital\Cms\Core\Tests\Package\Fixtures\PackageWithActions;
use Iddigital\Cms\Core\Tests\Package\Fixtures\PackageWithCharts;
use Iddigital\Cms\Core\Tests\Package\Fixtures\TestPackage;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CmsTest extends CmsTestCase
{
    /**
     * @var TestCms
     */
    protected $cms;

    public function setUp()
    {
        $this->cms = new TestCms(new MockingIocContainer($this));
    }

    public function testGetters()
    {
        $this->assertInstanceOf(IAuthSystem::class, $this->cms->getAuth());
        $this->assertInstanceOf(ILanguageProvider::class, $this->cms->getLang());
        $this->assertInstanceOf(MockingIocContainer::class, $this->cms->getIocContainer());
    }

    public function testPackageNames()
    {
        $this->assertSame(['test-package'], $this->cms->getPackageNames());
    }

    public function testHasPackage()
    {
        $this->assertSame(true, $this->cms->hasPackage('test-package'));
        $this->assertSame(false, $this->cms->hasPackage('non-existent'));
    }

    public function testLoadPackages()
    {
        $packages = $this->cms->loadPackages();

        $this->assertSame(['test-package'], array_keys($packages));

        $this->assertInstanceOf(TestPackage::class, $packages['test-package']);
    }

    public function testLoadPackage()
    {
        $this->assertInstanceOf(TestPackage::class, $package1 = $this->cms->loadPackage('test-package'));

        // Should cache packages
        $this->assertSame($package1, $this->cms->loadPackage('test-package'));

        $this->assertThrows(function () {
            $this->cms->loadPackage('non-existent');
        }, PackageNotInstalledException::class);
    }

    public function testLoadPermissionsInPackageNamespace()
    {
        /**
         * @see PackageWithActions Defined permissions in package
         */
        $this->assertEquals([
                Permission::named('test-package.test-module-with-actions.permission.name'),
                Permission::named('test-package.test-module-with-actions.permission.one'),
                Permission::named('test-package.test-module-with-actions.permission.two'),
        ], $this->cms->loadPermissions());
    }
}