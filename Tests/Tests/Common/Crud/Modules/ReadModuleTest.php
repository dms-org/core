<?php

namespace Iddigital\Cms\Core\Tests\Common\Crud\Modules;

use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Auth\Permission;
use Iddigital\Cms\Core\Auth\UserForbiddenException;
use Iddigital\Cms\Core\Common\Crud\Action\Crud\ViewDetailsAction;
use Iddigital\Cms\Core\Common\Crud\IReadModule;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Table\DataSource\ObjectTableDataSource;
use Iddigital\Cms\Core\Tests\Module\Mock\MockAuthSystem;
use Iddigital\Cms\Core\Tests\Module\ModuleTestBase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ReadModuleTest extends ModuleTestBase
{
    /**
     * @var IEntitySet
     */
    protected $dataSource;

    /**
     * @var IReadModule
     */
    protected $module;

    /**
     * @return IEntitySet
     */
    abstract protected function buildDataSource();

    /**
     * @inheritDoc
     */
    final protected function buildModule(MockAuthSystem $authSystem)
    {
        $this->dataSource = $this->buildDataSource();

        return $this->buildReadModule($this->dataSource, $authSystem);
    }

    /**
     * @param IEntitySet     $dataSource
     * @param MockAuthSystem $authSystem
     *
     * @return IReadModule
     */
    abstract protected function buildReadModule(IEntitySet $dataSource, MockAuthSystem $authSystem);

    /**
     * @return IPermission[]
     */
    final protected function expectedPermissions()
    {
        return array_merge($this->expectedReadModulePermissions(), [Permission::named(IReadModule::VIEW_PERMISSION)]);
    }

    /**
     * @return IPermission[]
     */
    abstract protected function expectedReadModulePermissions();

    public function testSummaryTableHasCorrectDataSource()
    {
        /** @var ObjectTableDataSource $tableDataSource */
        $tableDataSource = $this->module->getSummaryTable()->getDataSource();

        $this->assertInstanceOf(ObjectTableDataSource::class, $tableDataSource);
        $this->assertSame($this->dataSource, $tableDataSource->getObjectDataSource());
    }

    public function testSummaryTableActionRequiresViewPermission()
    {
        $permissions = $this->module->getSummaryTableAction()->getRequiredPermissions();

        $this->assertContains(Permission::named(IReadModule::VIEW_PERMISSION), $permissions);
    }

    public function testSummaryTableAction()
    {
        $this->assertSame($this->module->getSummaryTable(), $this->module->getSummaryTableAction()->run());

        $this->authSystem->setIsAuthorized(false);
        $this->assertThrows(function () {
            $this->module->getSummaryTableAction()->run();
        }, UserForbiddenException::class);
    }

    public function testObjectActions()
    {
        if ($this->module->allowsDetails()) {
            $this->assertTrue($this->module->hasObjectAction(IReadModule::DETAILS_ACTION));
            $this->assertInstanceOf(ViewDetailsAction::class, $this->module->getObjectAction(IReadModule::DETAILS_ACTION));
        }

        $this->assertFalse($this->module->hasObjectAction('non-existent'));
        $this->assertThrows(function () {
            $this->module->getObjectAction('non-existent');
        }, InvalidArgumentException::class);
    }
}