<?php

namespace Iddigital\Cms\Core\Tests\Package\Fixtures;

use Iddigital\Cms\Core\Package\Package;
use Iddigital\Cms\Core\Package\PackageDefinition;
use Iddigital\Cms\Core\Tests\Module\Fixtures\ModuleWithActions;
use Iddigital\Cms\Core\Tests\Module\Fixtures\ModuleWithCharts;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestPackage extends Package
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
        $package->name('test-package');

        $package->modules([
                'test-module-with-actions' => ModuleWithActions::class,
                'test-module-with-charts'  => ModuleWithCharts::class,
        ]);
    }
}