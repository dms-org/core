<?php

namespace Dms\Core\Tests\Package;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Tests\Helpers\Mock\MockingIocContainer;
use Dms\Core\Tests\Module\Fixtures\TestDto;
use Dms\Core\Tests\Package\Fixtures\InvalidModuleClassPackage;
use Dms\Core\Tests\Package\Fixtures\TestPackage;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidPackageTest extends CmsTestCase
{
    /**
     * @var InvalidModuleClassPackage
     */
    protected $package;

    public function setUp(): void
    {
        $this->package = new InvalidModuleClassPackage(new MockingIocContainer($this));
    }

    public function testInvalidModuleClass()
    {
        $e = $this->assertThrows(function ()  {
            $this->package->loadModule('invalid-module-class');
        }, InvalidArgumentException::class);

        $this->assertContains(TestDto::class, $e->getMessage());
    }

    public function testInvalidModuleName()
    {
        $e = $this->assertThrows(function () {
            $this->package->loadModule('invalid-module-name');
        }, InvalidArgumentException::class);

        $this->assertContains('invalid-module-name', $e->getMessage());
        $this->assertContains('test-module-with-actions', $e->getMessage());
    }
}