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

        $this->assertStringContainsString(\stdClass::class, $e->getMessage());
    }

    public function testInvalidPackageName()
    {
        $e = $this->assertThrows(function () {
            $this->package->loadPackage('invalid-package-name');
        }, InvalidArgumentException::class);

        $this->assertStringContainsString('invalid-package-name', $e->getMessage());
        $this->assertStringContainsString('test-package', $e->getMessage());
    }
}