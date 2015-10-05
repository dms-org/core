<?php

namespace Iddigital\Cms\Core\Tests\Module;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Module\Module;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ModuleTestBase extends CmsTestCase
{
    /**
     * @var Module
     */
    protected $module;

    public function setUp()
    {
        $this->module = $this->buildModule();
    }

    /**
     * @return Module
     */
    abstract protected function buildModule();

    /**
     * @return IPermission[]
     */
    abstract protected function expectedPermissions();

    /**
     * @return string
     */
    abstract protected function expectedName();

    public function testName()
    {
        $this->assertSame($this->expectedName(), $this->module->getName());
    }

    public function testPermissions()
    {
        $this->assertEquals($this->expectedPermissions(), array_values($this->module->getPermissions()));
    }
}