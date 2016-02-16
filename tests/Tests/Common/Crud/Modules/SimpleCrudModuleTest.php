<?php

namespace Dms\Core\Tests\Common\Crud\Modules;

use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\Permission;
use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\Form\FormWithBinding;
use Dms\Core\Common\Crud\ICrudModule;
use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Form\Stage\DependentFormStage;
use Dms\Core\Form\Stage\IndependentFormStage;
use Dms\Core\Module\IParameterizedAction;
use Dms\Core\Persistence\ArrayRepository;
use Dms\Core\Persistence\IRepository;
use Dms\Core\Tests\Common\Crud\Modules\Fixtures\Simple\SimpleCrudModule;
use Dms\Core\Tests\Common\Crud\Modules\Fixtures\Simple\SimpleEntity;
use Dms\Core\Tests\Module\Mock\MockAuthSystem;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SimpleCrudModuleTest extends CrudModuleTest
{

    /**
     * @return string
     */
    protected function expectedName()
    {
        return 'simple-crud-module';
    }

    /**
     * @return IRepository
     */
    protected function buildRepositoryDataSource()
    {
        return new ArrayRepository(SimpleEntity::collection([
                new SimpleEntity(1, 'abc'),
                new SimpleEntity(2, '123'),
                new SimpleEntity(3, 'xyz'),
        ]));
    }

    /**
     * @param IRepository    $dataSource
     * @param MockAuthSystem $authSystem
     *
     * @return IReadModule
     */
    protected function buildCrudModule(IRepository $dataSource, MockAuthSystem $authSystem)
    {
        return new SimpleCrudModule($dataSource, $authSystem);
    }

    /**
     * @return IPermission[]
     */
    protected function expectedReadModulePermissions()
    {
        return [
                Permission::named(ICrudModule::CREATE_PERMISSION),
                Permission::named(ICrudModule::EDIT_PERMISSION),
                Permission::named(ICrudModule::REMOVE_PERMISSION),
        ];
    }

    public function testSummaryTableData()
    {
        $data = $this->module->getSummaryTable()->loadView();
        $id   = IReadModule::SUMMARY_TABLE_ID_COLUMN;

        $this->assertDataTableEquals([
                [
                        [$id => 1, 'data' => 'abc'],
                        [$id => 2, 'data' => '123'],
                        [$id => 3, 'data' => 'xyz'],
                ],
        ], $data);
    }

    public function testCreateAction()
    {
        $this->assertTrue($this->module->allowsCreate());

        $action = $this->module->getCreateAction();

        $this->assertInstanceOf(IParameterizedAction::class, $action);
        $this->assertNotInstanceOf(IObjectAction::class, $action);

        $action->run([
                'data' => 'new!!'
        ]);

        $this->assertCount(4, $this->dataSource);
        $this->assertSame(['id' => 4, 'data' => 'new!!'], $this->dataSource->get(4)->toArray());

        $this->assertThrows(function () use ($action) {
            $action->run([
                    'data' => null
            ]);
        }, InvalidFormSubmissionException::class);
    }

    public function testEditInitialFormData()
    {
        $this->assertTrue($this->module->allowsEdit());

        $action = $this->module->getEditAction();

        /** @var FormWithBinding $form */
        $form   = $action
            ->withSubmittedFirstStage([IObjectAction::OBJECT_FIELD_NAME => $this->dataSource->get(1)])
            ->getStagedForm()
            ->getFirstForm();

        $this->assertSame(['data' => 'abc'], $form->getInitialValues());
    }

    public function testEditAction()
    {
        $this->assertTrue($this->module->allowsEdit());

        $action = $this->module->getEditAction();

        $this->assertInstanceOf(IObjectAction::class, $action);

        $action->run([
                IObjectAction::OBJECT_FIELD_NAME => 2,
                'data'                           => 'edited!!'
        ]);

        $this->assertSame(['id' => 2, 'data' => 'edited!!'], $this->dataSource->get(2)->toArray());

        $action->runOnObject($this->dataSource->get(3), [
                'data' => 'edited-also!!'
        ]);

        $this->assertSame(['id' => 3, 'data' => 'edited-also!!'], $this->dataSource->get(3)->toArray());

        $this->assertThrows(function () use ($action) {
            $invalidId = 5;
            $action->run([
                    IObjectAction::OBJECT_FIELD_NAME => $invalidId,
                    'data'                           => 'aa'
            ]);
        }, InvalidFormSubmissionException::class);
    }

    public function testEditFormStagesBecomeIndependentWithObjectSubmitted()
    {
        $this->assertTrue($this->module->allowsEdit());

        $action = $this->module->getEditAction();

        $stagedForm   = $action            ->getStagedForm();

        $this->assertSame(2, $stagedForm->getAmountOfStages());
        $this->assertInstanceOf(DependentFormStage::class, $stagedForm->getStage(2));

        $stagedForm   = $action
            ->withSubmittedFirstStage([IObjectAction::OBJECT_FIELD_NAME => $this->dataSource->get(1)])
            ->getStagedForm();

        $this->assertSame(1, $stagedForm->getAmountOfStages());
        $this->assertInstanceOf(IndependentFormStage::class, $stagedForm->getStage(1));
    }

    public function testRemoveAction()
    {
        $this->assertTrue($this->module->allowsEdit());

        $action = $this->module->getRemoveAction();

        $this->assertInstanceOf(IObjectAction::class, $action);

        $action->run([IObjectAction::OBJECT_FIELD_NAME => 1]);
        $this->assertCount(2, $this->dataSource);

        $action->run([IObjectAction::OBJECT_FIELD_NAME => 3]);
        $this->assertCount(1, $this->dataSource);

        $action->run([IObjectAction::OBJECT_FIELD_NAME => 2]);
        $this->assertCount(0, $this->dataSource);

        $this->assertThrows(function () use ($action) {
            $action->run([IObjectAction::OBJECT_FIELD_NAME => 2]);
        }, InvalidFormSubmissionException::class);
    }

    public function testCustomObjectAction()
    {
        $this->assertSame(true, $this->module->hasObjectAction('duplicate-data'));

        $action = $this->module->getObjectAction('duplicate-data');

        $this->assertSame('duplicate-data', $action->getName());
        $this->assertSame(SimpleEntity::class, $action->getObjectType());
        $this->assertSame(null, $action->getReturnTypeClass());
        $this->assertEquals(
                [Permission::named(ICrudModule::EDIT_PERMISSION)],
                array_values($action->getRequiredPermissions())
        );
        $this->assertEquals($this->dataSource->getAll(), $action->getSupportedObjects($this->dataSource->getAll()));

        $action->run([IObjectAction::OBJECT_FIELD_NAME => 1]);
        $this->assertSame('abcabc', $this->dataSource->get(1)->data);
        $this->assertSame('123', $this->dataSource->get(2)->data);
    }
}