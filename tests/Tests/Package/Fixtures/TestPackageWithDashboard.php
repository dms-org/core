<?php

namespace Dms\Core\Tests\Package\Fixtures;

use Dms\Core\Package\Definition\PackageDefinition;
use Dms\Core\Package\Package;
use Dms\Core\Tests\Module\Fixtures\ModuleWithWidgets;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestPackageWithDashboard extends Package
{
    /**
     * Defines the structure of this cms package.
     *
     * @param PackageDefinition $package
     *
     * @return void
     */
    protected function define(PackageDefinition $package)
    {
        $package->name('test-package-dashboard');

        $package->modules([
                'test-module-with-widgets' => ModuleWithWidgets::class,
        ]);

        $package->dashboard()
            ->widgets([
                'test-module-with-widgets.table-widget.with-criteria',
                'test-module-with-widgets.action-widget',
                'test-module-with-widgets.chart-widget.all',
            ]);
    }
}