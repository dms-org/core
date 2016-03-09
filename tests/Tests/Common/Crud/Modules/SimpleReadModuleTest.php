<?php

namespace Dms\Core\Tests\Common\Crud\Modules;

use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\AdminForbiddenException;
use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Form\IForm;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Form\IStagedForm;
use Dms\Core\Form\Stage\IndependentFormStage;
use Dms\Core\Model\IIdentifiableObjectSet;
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
     * @return IIdentifiableObjectSet
     */
    protected function buildDataSource() : IIdentifiableObjectSet
    {
        return SimpleEntity::collection([
            new SimpleEntity(1, 'abc'),
            new SimpleEntity(2, '123'),
            new SimpleEntity(3, 'xyz'),
        ]);
    }

    /**
     * @param IIdentifiableObjectSet $dataSource
     * @param MockAuthSystem         $authSystem
     *
     * @return IReadModule
     */
    protected function buildReadModule(IIdentifiableObjectSet $dataSource, MockAuthSystem $authSystem) : IReadModule
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

        /** @var IStagedForm $detailsForm */
        $detailsForm = $this->module->getDetailsAction()->run([IObjectAction::OBJECT_FIELD_NAME => 1]);

        $this->assertInstanceOf(IStagedForm::class, $detailsForm);
        $this->assertSame(2, $detailsForm->getAmountOfStages());
        $this->assertContainsOnlyInstancesOf(IndependentFormStage::class, $detailsForm->getAllStages());

        $this->assertSame($this->dataSource->get(1), $detailsForm->getFirstForm()->getField(IObjectAction::OBJECT_FIELD_NAME)->getInitialValue());

        $this->assertSame(['data'], $detailsForm->getStage(2)->loadForm()->getFieldNames());
        $this->assertSame([
            'data' => 'abc',
        ], $detailsForm->getStage(2)->loadForm()->getInitialValues());

        $this->assertThrows(function () {
            $nonExistentId = 5;
            $this->module->getDetailsAction()->run([IObjectAction::OBJECT_FIELD_NAME => $nonExistentId]);
        }, InvalidFormSubmissionException::class);

        $this->authSystem->setIsAuthorized(false);
        $this->assertThrows(function () {
            $this->module->getDetailsAction()->run([IObjectAction::OBJECT_FIELD_NAME => 1]);
        }, AdminForbiddenException::class);
    }
}