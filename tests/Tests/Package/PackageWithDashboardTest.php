<?php

namespace Dms\Core\Tests\Package;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Tests\Helpers\Mock\MockingIocContainer;
use Dms\Core\Tests\Package\Fixtures\TestPackageWithDashboard;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PackageWithDashboardTest extends CmsTestCase
{
    /**
     * @var TestPackageWithDashboard
     */
    protected $package;

    public function setUp()
    {
        $this->package = new TestPackageWithDashboard(new MockingIocContainer($this));
    }

    public function testDashboard()
    {
        $this->assertSame(true, $this->package->hasDashboard());
        $dashboard = $this->package->loadDashboard();

        $this->assertSame([
                $this->package->loadModule('test-module-with-widgets')->getWidget('table-widget.with-criteria'),
                $this->package->loadModule('test-module-with-widgets')->getWidget('action-widget'),
                $this->package->loadModule('test-module-with-widgets')->getWidget('chart-widget.all'),
        ], $dashboard->getWidgets());
    }
}