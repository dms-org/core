<?php

namespace Iddigital\Cms\Core\Tests\Cms\Fixtures;

use Iddigital\Cms\Core\Cms;
use Iddigital\Cms\Core\CmsDefinition;
use Iddigital\Cms\Core\Tests\Package\Fixtures\TestPackage;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidPackageCms extends Cms
{

    /**
     * Defines the structure and installed packages of the cms.
     *
     * @param CmsDefinition $cms
     *
     * @return void
     */
    protected function define(CmsDefinition $cms)
    {
        $cms->packages([
                'invalid-package-class' => \stdClass::class,
                'invalid-package-name'  => TestPackage::class,
        ]);
    }
}