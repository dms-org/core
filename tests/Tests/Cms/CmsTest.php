<?php

namespace Dms\Core\Tests\Cms;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Auth\IAdmin;
use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\Permission;
use Dms\Core\Language\ILanguageProvider;
use Dms\Core\Package\PackageNotFoundException;
use Dms\Core\Tests\Cms\Fixtures\TestCms;
use Dms\Core\Tests\Helpers\Mock\MockingIocContainer;
use Dms\Core\Tests\Module\Mock\MockAuthSystem;
use Dms\Core\Tests\Package\Fixtures\TestPackage;
use Psr\Cache\CacheItemPoolInterface;

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

        $this->cms->getIocContainer()->bindValue(
            IAuthSystem::class,
            new MockAuthSystem($this->getMockForAbstractClass(IAdmin::class), $this, '')
        );
    }

    public function testGetters()
    {
        $this->assertInstanceOf(IAuthSystem::class, $this->cms->getAuth());
        $this->assertInstanceOf(ILanguageProvider::class, $this->cms->getLang());
        $this->assertInstanceOf(CacheItemPoolInterface::class, $this->cms->getCache());
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
        }, PackageNotFoundException::class);
    }

    public function testLoadPermissionsInPackageNamespace()
    {
        /**
         * @see PackageWithActions Defined permissions in package
         */
        $this->assertEquals([
                Permission::named('test-package.test-module-with-actions.some-permission'),
                Permission::named('test-package.test-module-with-actions.permission.name'),
                Permission::named('test-package.test-module-with-actions.permission.one'),
                Permission::named('test-package.test-module-with-actions.permission.two'),
        ], $this->cms->loadPermissions());
    }
}