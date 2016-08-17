<?php

namespace Dms\Core\Tests\Package\Fixtures;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Module\Definition\ModuleDefinition;
use Dms\Core\Module\Module;
use Dms\Core\Package\Definition\PackageDefinition;
use Dms\Core\Package\Package;
use Dms\Core\Tests\Module\Fixtures\ModuleWithActions;
use Dms\Core\Tests\Module\Fixtures\ModuleWithCharts;

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

        $package->metadata([
            'key' => 'some-metadata',
        ]);

        $package->metadata([
            'another-key' => 'some-more-metadata',
        ]);

        $package->modules([
            'test-module-with-actions' => ModuleWithActions::class,
            'test-module-with-charts'  => ModuleWithCharts::class,
            'test-module-factory'      => function (TestPackage $package, string $moduleName) {
                return new class($package->getIocContainer()->get(IAuthSystem::class)) extends Module
                {
                    protected function define(ModuleDefinition $module)
                    {
                        $module->name('test-module-factory');
                    }
                };
            },
        ]);
    }
}