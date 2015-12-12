<?php

namespace Iddigital\Cms\Core\Tests\Package\Fixtures;

use Iddigital\Cms\Core\Package\Package;
use Iddigital\Cms\Core\Package\PackageDefinition;
use Iddigital\Cms\Core\Tests\Module\Fixtures\ModuleWithActions;
use Iddigital\Cms\Core\Tests\Module\Fixtures\TestDto;

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