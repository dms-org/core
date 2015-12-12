<?php

namespace Iddigital\Cms\Core\Tests\Package;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Tests\Cms\Fixtures\InvalidPackageCms;
use Iddigital\Cms\Core\Tests\Helpers\Mock\MockingIocContainer;
use Iddigital\Cms\Core\Tests\Module\Fixtures\TestDto;
use Iddigital\Cms\Core\Tests\Package\Fixtures\InvalidModuleClassPackage;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidCmsTest extends CmsTestCase
{
    /**
     * @var InvalidPackageCms
     */
    protected $package;

    public function setUp()
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