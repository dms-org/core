<?php

namespace Dms\Core\Tests\Common\Crud\Modules;

use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\UserForbiddenException;
use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Form\IForm;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Tests\Common\Crud\Modules\Fixtures\Simple\SimpleEntity;
use Dms\Core\Tests\Common\Crud\Modules\Fixtures\Simple\SimpleReadModule;
use Dms\Core\Tests\Module\Mock\MockAuthSystem;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SimpleReadModuleTest extends ReadModuleTest
{

    /**
     * @return string
     */
    protected function expectedName()
    {
        return 'simple-read-module';
    }

    /**
     * @return IEntitySet
     */
    protected function buildDataSource()
    {
        return SimpleEntity::collection([
            new SimpleEntity(1, 'abc'),
            new SimpleEntity(2, '123'),
            new SimpleEntity(3, 'xyz'),
        ]);
    }

    /**
     * @param IEntitySet     $dataSource
     * @param MockAuthSystem $authSystem
     *
     * @return IReadModule
     */
    protected function buildReadModule(IEntitySet $dataSource, MockAuthSystem $authSystem)
    {
        return new SimpleReadModule($dataSource, $authSystem);
    }

    /**
     * @return IPermission[]
     */
    protected function expectedReadModulePermissions()
    {
        return [];
    }

    public function testSummaryTableStructure()
    {
        $tableDataSource = $this->module->getSummaryTable()->getDataSource();

        $this->assertSame(false, $tableDataSource->getStructure()->getColumn('data')->isHidden());
        $this->assertSame(true, $tableDataSource->getStructure()->getColumn('id')->isHidden());
    }

    public function testSummaryTableData()
    {
        $data = $this->module->getSummaryTable()->loadView();
        $id   = IReadModule::SUMMARY_TABLE_ID_COLUMN;

        $this->assertDataTableEquals([
            [
                [$id => [$id => 1], 'data' => ['data' => 'abc']],
                [$id => [$id => 2], 'data' => ['data' => '123']],
                [$id => [$id => 3], 'data' => ['data' => 'xyz']],
            ],
        ], $data);
    }

    public function testDetailsAction()
    {
        $this->assertTrue($this->module->allowsDetails());

        /** @var IForm $detailsForm */
        $detailsForm = $this->module->getDetailsAction()->run([IObjectAction::OBJECT_FIELD_NAME => 1]);

        $this->assertInstanceOf(IForm::class, $detailsForm);
        $this->assertSame(['data'], $detailsForm->getFieldNames());
        $this->assertSame([
            'data' => 'abc',
        ], $detailsForm->getInitialValues());

        $this->assertThrows(function () {
            $nonExistentId = 5;
            $this->module->getDetailsAction()->run([IObjectAction::OBJECT_FIELD_NAME => $nonExistentId]);
        }, InvalidFormSubmissionException::class);

        $this->authSystem->setIsAuthorized(false);
        $this->assertThrows(function () {
            $this->module->getDetailsAction()->run([IObjectAction::OBJECT_FIELD_NAME => 1]);
        }, UserForbiddenException::class);
    }
}