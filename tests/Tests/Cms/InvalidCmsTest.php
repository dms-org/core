<?php

namespace Dms\Core\Tests\Package;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Tests\Cms\Fixtures\InvalidPackageCms;
use Dms\Core\Tests\Helpers\Mock\MockingIocContainer;
use Dms\Core\Tests\Module\Fixtures\TestDto;
use Dms\Core\Tests\Package\Fixtures\InvalidModuleClassPackage;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidCmsTest extends CmsTestCase
{
    /**
     * @var InvalidPackageCms
     */
    protected $package;

    public function setUp(): void
    {
        $this->package = new InvalidPackageCms(new MockingIocContainer($this));
    }

    public function testInvalidPackageClass()
    {
        $e = $this->assertThrows(function () {
            $this->package->loadPackage('invalid-package-class');
        }, InvalidArgumentException::class);

        $this->assertContains(\stdClass::class, $e->getMessage());
    }

    public function testInvalidPackageName()
    {
        $e = $this->assertThrows(function () {
            $this->package->loadPackage('invalid-package-name');
        }, InvalidArgumentException::class);

        $this->assertContains('invalid-package-name', $e->getMessage());
        $this->assertContains('test-package', $e->getMessage());
    }
}