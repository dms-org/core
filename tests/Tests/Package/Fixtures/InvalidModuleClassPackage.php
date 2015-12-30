<?php

namespace Dms\Core\Tests\Package\Fixtures;

use Dms\Core\Package\Definition\PackageDefinition;
use Dms\Core\Package\Package;
use Dms\Core\Tests\Module\Fixtures\ModuleWithActions;
use Dms\Core\Tests\Module\Fixtures\TestDto;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidModuleClassPackage extends Package
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
        $package->name('invalid-package');

        $package->modules([
                'invalid-module-class' => TestDto::class,
                'invalid-module-name' => ModuleWithActions::class,
        ]);
    }
}