<?php

namespace Iddigital\Cms\Core\Tests\Module;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Auth\IUser;
use Iddigital\Cms\Core\Module\Module;
use Iddigital\Cms\Core\Table\IDataTable;
use Iddigital\Cms\Core\Tests\Module\Mock\MockAuthSystem;
use Iddigital\Cms\Core\Tests\Table\DataSource\DataTableHelper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ModuleTestBase extends CmsTestCase
{
    /**
     * @var MockAuthSystem
     */
    protected $authSystem;

    /**
     * @var Module
     */
    protected $module;

    public function setUp()
    {
        $this->authSystem = new MockAuthSystem($this->getMockForAbstractClass(IUser::class));
        $this->module = $this->buildModule($this->authSystem);
    }

    /**
     * @param MockAuthSystem $authSystem
     *
     * @return Module
     */
    abstract protected function buildModule(MockAuthSystem $authSystem);

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
        $expected = $this->expectedPermissions();
        $actual = array_values($this->module->getPermissions());
        sort($expected, SORT_REGULAR);
        sort($actual, SORT_REGULAR);
        $this->assertEquals($expected, $actual);
    }

    protected function assertDataTableEquals(array $expectedSections, IDataTable $dataTable)
    {
        $this->assertEquals(
                DataTableHelper::normalizeSingleComponents($expectedSections),
                DataTableHelper::covertDataTableToNormalizedArray($dataTable)
        );
    }
}