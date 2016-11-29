<?php

namespace Dms\Core\Tests\Cms\Fixtures;

use Dms\Core\Cms;
use Dms\Core\CmsDefinition;
use Dms\Core\Tests\Package\Fixtures\TestPackage;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestCms extends Cms
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
            'test-package'         => TestPackage::class,
            'test-package-factory' => function (self $cms) {
                return new TestPackage($cms->getIocContainer(), 'test-package-factory');
            },
        ]);
    }
}