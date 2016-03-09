<?php

namespace Dms\Core\Tests\Common\Crud\Modules;

use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\Permission;
use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\Action\Table\IReorderAction;
use Dms\Core\Common\Crud\ICrudModule;
use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Model\IMutableObjectSet;
use Dms\Core\Module\IParameterizedAction;
use Dms\Core\Persistence\ArrayRepository;
use Dms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\Domain\Adult;
use Dms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\Domain\Child;
use Dms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\Domain\Person;
use Dms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\Domain\TestColour;
use Dms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\PersonModule;
use Dms\Core\Tests\Module\Mock\MockAuthSystem;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PersonCrudModuleTest extends CrudModuleTest
{
    /**
     * @var ArrayRepository
     */
    protected $dataSource;

    /**
     * @return string
     */
    protected function expectedName()
    {
        return 'people-module';
    }

    /**
     * @return IMutableObjectSet
     */
    protected function buildRepositoryDataSource() : IMutableObjectSet
    {
        return new ArrayRepository(Person::collection([
            new Child(1, 'Jack', 'Baz', 15, TestColour::blue()),
            new Child(2, 'Samantha', 'Williams', 12, TestColour::red()),
            new Child(3, 'Casey', 'Low', 15, TestColour::green()),
            //
            new Adult(4, 'Joe', 'Quarter', 25, 'Surgeon'),
            new Adult(5, 'Kate', 'Costa', 28, 'Lawyer'),
        ]));
    }

    /**
     * @param IMutableObjectSet $dataSource
     * @param MockAuthSystem    $authSystem
     *
     * @return ICrudModule
     */
    protected function buildCrudModule(IMutableObjectSet $dataSource, MockAuthSystem $authSystem) : ICrudModule
    {
        return new PersonModule($dataSource, $authSystem);
    }

    /**
     * @return IPermission[]
     */
    protected function expectedReadModulePermissions()
    {
        return [
            Permission::named('random-permission'),
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
        return [Permission::named('random-permission')];
    }


    public function testContinuedSectionsInDependentStagesOnCreateForm()
    {
        $form = $this->module->getCreateAction()
            ->getStagedForm();

        $firstStageForm = $form->getStage(1)->loadForm();

        $this->assertCount(1, $firstStageForm->getSections());
        $this->assertSame(['first_name', 'last_name', 'age'], $firstStageForm->getFieldNames());
    }

    public function testContinuedSectionsInDependentStagesOnEditForm()
    {
        $form = $this->module->getEditAction()
            ->submitFirstStage([IObjectAction::OBJECT_FIELD_NAME => 1])
            ->getStagedForm();

        $firstStageForm  = $form->getStage(1)->loadForm();
        $secondStageForm = $form->getStage(2)->loadForm([]);

        $this->assertCount(1, $firstStageForm->getSections());
        $this->assertCount(1, $secondStageForm->getSections());

        $this->assertSame(false, $firstStageForm->getSections()[0]->doesContinuePreviousSection());
        $this->assertSame(true, $secondStageForm->getSections()[0]->doesContinuePreviousSection());
    }

    public function testSummaryTableData()
    {
        $data = $this->module->getSummaryTable()->loadView();
        $id   = IReadModule::SUMMARY_TABLE_ID_COLUMN;

        $this->assertDataTableEquals([
            [
                [$id => 1, 'type' => 'child', 'name' => ['first' => 'Jack', 'last' => 'Baz'], 'age' => 15],
                [$id => 2, 'type' => 'child', 'name' => ['first' => 'Samantha', 'last' => 'Williams'], 'age' => 12],
                [$id => 3, 'type' => 'child', 'name' => ['first' => 'Casey', 'last' => 'Low'], 'age' => 15],
                //
                [$id => 4, 'type' => 'adult', 'name' => ['first' => 'Joe', 'last' => 'Quarter'], 'age' => 25],
                [$id => 5, 'type' => 'adult', 'name' => ['first' => 'Kate', 'last' => 'Costa'], 'age' => 28],
            ],
        ], $data);
    }

    public function testSummaryTableDataInGroups()
    {
        $data = $this->module->getSummaryTable()->loadView('grouped-by-type');
        $id   = IReadModule::SUMMARY_TABLE_ID_COLUMN;

        $this->assertDataTableEquals([
            [
                'group_data' => ['type' => 'child'],
                [$id => 1, 'type' => 'child', 'name' => ['first' => 'Jack', 'last' => 'Baz'], 'age' => 15],
                [$id => 2, 'type' => 'child', 'name' => ['first' => 'Samantha', 'last' => 'Williams'], 'age' => 12],
                [$id => 3, 'type' => 'child', 'name' => ['first' => 'Casey', 'last' => 'Low'], 'age' => 15],
            ],
            [
                'group_data' => ['type' => 'adult'],
                [$id => 4, 'type' => 'adult', 'name' => ['first' => 'Joe', 'last' => 'Quarter'], 'age' => 25],
                [$id => 5, 'type' => 'adult', 'name' => ['first' => 'Kate', 'last' => 'Costa'], 'age' => 28],
            ],
        ], $data);
    }

    public function testReorderAction()
    {
        $this->assertTrue($this->module->getSummaryTable()->hasReorderAction('default'));
        $this->assertTrue($this->module->hasObjectAction('summary-table.default.reorder'));

        /** @var IReorderAction $reorderAction */
        $reorderAction = $this->module->getSummaryTable()->getReorderAction('default');

        $this->assertInstanceOf(IReorderAction::class, $reorderAction);
        $this->assertEquals([
            Permission::named(IReadModule::VIEW_PERMISSION),
            Permission::named(ICrudModule::EDIT_PERMISSION),
        ], array_values($reorderAction->getRequiredPermissions()));

        $reorderAction->run([
            IObjectAction::OBJECT_FIELD_NAME     => 1,
            IReorderAction::NEW_INDEX_FIELD_NAME => 2,
        ]);

        $this->assertSame(
            [2, 1, 3, 4, 5],
            $this->dataSource->getCollection()
                ->select(function (Person $person) {
                    return $person->getId();
                })
                ->asArray()
        );

        $reorderAction->runReorder($this->dataSource->get(1), 4);

        $this->assertSame(
            [2, 3, 4, 1, 5],
            $this->dataSource->getCollection()
                ->select(function (Person $person) {
                    return $person->getId();
                })
                ->asArray()
        );

        $this->assertThrows(function () use ($reorderAction) {
            $reorderAction->run([
                IObjectAction::OBJECT_FIELD_NAME => 1,
            ]);
        }, InvalidFormSubmissionException::class);
    }

    public function testCreateChild()
    {
        $this->assertTrue($this->module->allowsCreate());

        $action = $this->module->getCreateAction();

        $this->assertInstanceOf(IParameterizedAction::class, $action);

        $action->run([
            'first_name'       => 'New',
            'last_name'        => 'Kid',
            'age'              => '10',
            'favourite_colour' => 'yellow',
        ]);

        $this->assertCount(6, $this->dataSource);
        $this->assertEquals(
            [
                Child::ID               => 6,
                Child::FIRST_NAME       => 'New',
                Child::LAST_NAME        => 'Kid',
                Child::AGE              => 10,
                Child::FAVOURITE_COLOUR => TestColour::yellow(),
            ],
            $this->dataSource->get(6)->toArray()
        );
    }

    public function testCreateAdult()
    {
        $this->assertTrue($this->module->allowsCreate());

        $action = $this->module->getCreateAction();

        $this->assertInstanceOf(IParameterizedAction::class, $action);

        $action->run([
            'first_name' => 'New',
            'last_name'  => 'Adult',
            'age'        => '40',
            'profession' => 'Brick Layer',
        ]);

        $this->assertCount(6, $this->dataSource);
        $this->assertEquals(
            [
                Adult::ID         => 6,
                Adult::FIRST_NAME => 'New',
                Adult::LAST_NAME  => 'Adult',
                Adult::AGE        => 40,
                Adult::PROFESSION => 'Brick Layer',
            ],
            $this->dataSource->get(6)->toArray()
        );
    }

    public function testEditChildAction()
    {
        $this->assertTrue($this->module->allowsEdit());

        $action = $this->module->getEditAction();

        $this->assertInstanceOf(IObjectAction::class, $action);

        $action->run([
            IObjectAction::OBJECT_FIELD_NAME => 1,
            'first_name'                     => 'Jack',
            'last_name'                      => 'Baz',
            'age'                            => '15',
            'favourite_colour'               => 'red',
        ]);

        $this->assertEquals(TestColour::red(), $this->dataSource->get(2)->{Child::FAVOURITE_COLOUR});
    }

    public function testEditAdultAction()
    {
        $this->assertTrue($this->module->allowsEdit());

        $action = $this->module->getEditAction();

        $this->assertInstanceOf(IObjectAction::class, $action);

        $action->run([
            IObjectAction::OBJECT_FIELD_NAME => 5,
            'first_name'                     => 'Kate',
            'last_name'                      => 'Costa',
            'age'                            => '29',
            'profession'                     => 'Nurse',
        ]);

        $this->assertSame(
            [Adult::ID => 5, Adult::FIRST_NAME => 'Kate', Adult::LAST_NAME => 'Costa', Adult::AGE => 29, Adult::PROFESSION => 'Nurse'],
            $this->dataSource->get(5)->toArray()
        );
    }

    public function testEditingChildToBeAnAdultsAgeThrowsInvalidForm()
    {
        $this->setExpectedException(InvalidFormSubmissionException::class);

        $this->module->getEditAction()->run([
            IObjectAction::OBJECT_FIELD_NAME => 1,
            'first_name'                     => 'Jack',
            'last_name'                      => 'Baz',
            'age'                            => '30',
            'favourite_colour'               => 'blue',
        ]);
    }

    public function testEditingAdultToBeChildThrowsInvalidForm()
    {
        $this->setExpectedException(InvalidFormSubmissionException::class);

        $this->module->getEditAction()->run([
            IObjectAction::OBJECT_FIELD_NAME => 5,
            'first_name'                     => 'Kate',
            'last_name'                      => 'Costa',
            'age'                            => '14',
            'profession'                     => 'Lawyer',
        ]);
    }

    public function testRemoveAction()
    {
        $this->assertTrue($this->module->allowsEdit());

        $action = $this->module->getRemoveAction();

        $this->assertInstanceOf(IObjectAction::class, $action);

        $person = $action->run([IObjectAction::OBJECT_FIELD_NAME => 1]);
        $this->assertInstanceOf(Person::class, $person);
        $this->assertSame(1, $person->getId());
        $this->assertCount(4, $this->dataSource);

        $person = $action->run([IObjectAction::OBJECT_FIELD_NAME => 5]);
        $this->assertInstanceOf(Person::class, $person);
        $this->assertSame(5, $person->getId());
        $this->assertCount(3, $this->dataSource);
    }

    public function testCustomObjectActionWithFilter()
    {
        $this->assertSame(true, $this->module->hasObjectAction('swap-names'));

        $action = $this->module->getObjectAction('swap-names');

        $this->assertSame('swap-names', $action->getName());
        $this->assertSame(Person::class, $action->getObjectType());
        $this->assertSame(null, $action->getReturnTypeClass());
        $this->assertEquals(
            [Permission::named(ICrudModule::EDIT_PERMISSION)],
            array_values($action->getRequiredPermissions())
        );
        $this->assertEquals(
            $this->dataSource->matching(
                $this->dataSource->criteria()->whereInstanceOf(Adult::class)
            ),
            $action->getSupportedObjects($this->dataSource->getAll())
        );

        $action->run([
            IObjectAction::OBJECT_FIELD_NAME => 4,
            'swap_with'                      => 5,
        ]);

        $this->assertSame('Kate Costa', $this->dataSource->get(4)->getFullName());
        $this->assertSame('Joe Quarter', $this->dataSource->get(5)->getFullName());


        $this->assertThrows(function () use ($action) {

            $action->run([
                IObjectAction::OBJECT_FIELD_NAME => 1,
                'swap_with'                      => 5,
            ]);
        }, InvalidFormSubmissionException::class);
    }
}