<?php

namespace Dms\Core\Tests\Package;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Package\DashboardWidget;
use Dms\Core\Tests\Helpers\Mock\MockingIocContainer;
use Dms\Core\Tests\Package\Fixtures\TestPackageWithDashboardWithAlternateWidgetSyntax;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PackageWithDashboardWithAlternateWidgetSyntaxTest extends CmsTestCase
{
    /**
     * @var TestPackageWithDashboardWithAlternateWidgetSyntax
     */
    protected $package;

    public function setUp(): void
    {
        $this->package = new TestPackageWithDashboardWithAlternateWidgetSyntax(new MockingIocContainer($this));
    }

    public function testDashboard()
    {
        $this->assertSame(true, $this->package->hasDashboard());
        $dashboard = $this->package->loadDashboard();

        $this->assertEquals([
            $this->loadDashboardWidget('test-module-with-widgets', 'table-widget.all'),
            $this->loadDashboardWidget('test-module-with-widgets', 'table-widget.with-criteria'),
            $this->loadDashboardWidget('test-module-with-widgets', 'chart-widget.all'),
            $this->loadDashboardWidget('test-module-with-widgets', 'chart-widget.with-criteria'),
            $this->loadDashboardWidget('test-module-with-widgets', 'action-widget'),
            $this->loadDashboardWidget('test-module-with-widgets', 'form-data-widget'),
        ], $dashboard->getWidgets());
    }

    protected function loadDashboardWidget(string $moduleName, string $widgetName) : DashboardWidget
    {
        $module = $this->package->loadModule($moduleName);
        $widget = $module->getWidget($widgetName);

        return new DashboardWidget($module, $widget);
    }
}