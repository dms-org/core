<?php

namespace Iddigital\Cms\Core\Tests\Package;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Tests\Helpers\Mock\MockingIocContainer;
use Iddigital\Cms\Core\Tests\Module\Fixtures\TestDto;
use Iddigital\Cms\Core\Tests\Package\Fixtures\InvalidModuleClassPackage;
use Iddigital\Cms\Core\Tests\Package\Fixtures\TestPackage;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidPackageTest extends CmsTestCase
{
    /**
     * @var InvalidModuleClassPackage
     */
    protected $package;

    public function setUp()
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