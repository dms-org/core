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
use Dms\Core\Model\IMutableObjectSet;
use Dms\Core\Module\IParameterizedAction;
use Dms\Core\Tests\Common\Crud\Modules\Fixtures\ValueObject\SimpleValueObject;
use Dms\Core\Tests\Common\Crud\Modules\Fixtures\ValueObject\SimpleValueObjectCrudModule;
use Dms\Core\Tests\Module\Mock\MockAuthSystem;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SimpleValueObjectCrudModuleTest extends CrudModuleTest
{

    /**
     * @return string
     */
    protected function expectedName()
    {
        return 'value-object-crud-module';
    }

    /**
     * @return IMutableObjectSet
     */
    protected function buildRepositoryDataSource() : IMutableObjectSet
    {
        return SimpleValueObject::collection([
            new SimpleValueObject('abc'),
            new SimpleValueObject('123'),
            new SimpleValueObject('xyz'),
        ]);
    }

    /**
     * @param IMutableObjectSet $dataSource
     * @param MockAuthSystem    $authSystem
     *
     * @return ICrudModule
     */
    protected function buildCrudModule(IMutableObjectSet $dataSource, MockAuthSystem $authSystem) : ICrudModule
    {
        return new SimpleValueObjectCrudModule($dataSource, $authSystem);
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

    /**
     * @inheritDoc
     */
    protected function expectedReadModuleRequiredPermissions()
    {
        return [];
    }

    public function testSummaryTableData()
    {
        $data = $this->module->getSummaryTable()->loadView();

        // The id will use the index of the value object in the collection
        $id = IReadModule::SUMMARY_TABLE_ID_COLUMN;

        $this->assertDataTableEquals([
            [
                [$id => 0, 'data' => 'abc'],
                [$id => 1, 'data' => '123'],
                [$id => 2, 'data' => 'xyz'],
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
            'data' => 'new!!',
        ]);

        $this->assertCount(4, $this->dataSource);
        $this->assertSame(['data' => 'new!!'], $this->dataSource->get(3)->toArray());

        $this->assertThrows(function () use ($action) {
            $action->run([
                'data' => null,
            ]);
        }, InvalidFormSubmissionException::class);
    }

    public function testEditInitialFormData()
    {
        $this->assertTrue($this->module->allowsEdit());

        $action = $this->module->getEditAction();

        /** @var FormWithBinding $form */
        $form = $action
            ->withSubmittedFirstStage([IObjectAction::OBJECT_FIELD_NAME => $this->dataSource->get(0)])
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
            IObjectAction::OBJECT_FIELD_NAME => 1,
            'data'                           => 'edited!!',
        ]);

        $this->assertSame(['data' => 'edited!!'], $this->dataSource->get(1)->toArray());

        $action->runOnObject($this->dataSource->get(2), [
            'data' => 'edited-also!!',
        ]);

        $this->assertSame(['data' => 'edited-also!!'], $this->dataSource->get(2)->toArray());

        $this->assertThrows(function () use ($action) {
            $invalidId = 5;
            $action->run([
                IObjectAction::OBJECT_FIELD_NAME => $invalidId,
                'data'                           => 'aa',
            ]);
        }, InvalidFormSubmissionException::class);
    }

    public function testEditFormStagesBecomeIndependentWithObjectSubmitted()
    {
        $this->assertTrue($this->module->allowsEdit());

        $action = $this->module->getEditAction();

        $stagedForm = $action->getStagedForm();

        $this->assertSame(2, $stagedForm->getAmountOfStages());
        $this->assertInstanceOf(DependentFormStage::class, $stagedForm->getStage(2));

        $stagedForm = $action
            ->withSubmittedFirstStage([IObjectAction::OBJECT_FIELD_NAME => $this->dataSource->get(0)])
            ->getStagedForm();

        $this->assertSame(1, $stagedForm->getAmountOfStages());
        $this->assertInstanceOf(IndependentFormStage::class, $stagedForm->getStage(1));
    }

    public function testRemoveAction()
    {
        $this->assertTrue($this->module->allowsEdit());

        $action = $this->module->getRemoveAction();

        $this->assertInstanceOf(IObjectAction::class, $action);

        $action->run([IObjectAction::OBJECT_FIELD_NAME => 0]);
        $this->assertCount(2, $this->dataSource);

        $action->run([IObjectAction::OBJECT_FIELD_NAME => 2]);
        $this->assertCount(1, $this->dataSource);

        $action->run([IObjectAction::OBJECT_FIELD_NAME => 1]);
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
        $this->assertSame(SimpleValueObject::class, $action->getObjectType());
        $this->assertSame(null, $action->getReturnTypeClass());
        $this->assertEquals(
            [Permission::named(ICrudModule::EDIT_PERMISSION)],
            array_values($action->getRequiredPermissions())
        );
        $this->assertEquals($this->dataSource->getAll(), $action->getSupportedObjects($this->dataSource->getAll()));

        $action->run([IObjectAction::OBJECT_FIELD_NAME => 0]);
        $this->assertSame('abcabc', $this->dataSource->get(0)->data);
        $this->assertSame('123', $this->dataSource->get(1)->data);
    }
}