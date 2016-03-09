<?php

namespace Dms\Core\Tests\Module;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\IAdmin;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Module\IAction;
use Dms\Core\Module\Module;
use Dms\Core\Table\IDataTable;
use Dms\Core\Tests\Module\Mock\MockAuthSystem;
use Dms\Core\Tests\Table\DataSource\DataTableHelper;

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
        $this->authSystem = new MockAuthSystem($this->getMockForAbstractClass(IAdmin::class));
        $this->module     = $this->buildModule($this->authSystem);
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

    public function testPermissions($extraNamespace = null)
    {
        $expected = $this->expectedPermissions();

        foreach ($expected as $key => $permission) {
            $permission = $permission->inNamespace($this->module->getName());
            if ($extraNamespace) {
                $permission = $permission->inNamespace($extraNamespace);
            }

            $expected[$key] = $permission;
        }

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

    public function testPackageName()
    {
        $this->assertSame(null, $this->module->getPackageName());

        $this->module->setPackageName('some-package');

        $this->assertSame('some-package', $this->module->getPackageName());
        $this->testPermissions('some-package');

        $this->assertThrows(function () {
            $this->module->setPackageName('abc');
        }, InvalidOperationException::class);

        /** @var IAction $action */
        foreach ($this->module->getActions() as $action) {
            $this->assertSame('some-package', $action->getPackageName());
        }
    }
}