<?php

namespace Iddigital\Cms\Core\Tests\Common\Crud\Modules;

use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Auth\Permission;
use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectAction;
use Iddigital\Cms\Core\Common\Crud\Action\Table\IReorderAction;
use Iddigital\Cms\Core\Common\Crud\ICrudModule;
use Iddigital\Cms\Core\Common\Crud\IReadModule;
use Iddigital\Cms\Core\Form\InvalidFormSubmissionException;
use Iddigital\Cms\Core\Module\IParameterizedAction;
use Iddigital\Cms\Core\Persistence\ArrayRepository;
use Iddigital\Cms\Core\Persistence\IRepository;
use Iddigital\Cms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\Domain\Adult;
use Iddigital\Cms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\Domain\Child;
use Iddigital\Cms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\Domain\Colour;
use Iddigital\Cms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\Domain\Person;
use Iddigital\Cms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\PersonModule;
use Iddigital\Cms\Core\Tests\Module\Mock\MockAuthSystem;

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
     * @return IRepository
     */
    protected function buildRepositoryDataSource()
    {
        return new ArrayRepository(Person::collection([
                new Child(1, 'Jack', 'Baz', 15, Colour::blue()),
                new Child(2, 'Samantha', 'Williams', 12, Colour::red()),
                new Child(3, 'Casey', 'Low', 15, Colour::green()),
                //
                new Adult(4, 'Joe', 'Quarter', 25, 'Surgeon'),
                new Adult(5, 'Kate', 'Costa', 28, 'Lawyer'),
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
        return new PersonModule($dataSource, $authSystem);
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
                        Child::FAVOURITE_COLOUR => Colour::yellow()
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
                        Adult::PROFESSION => 'Brick Layer'
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

        $this->assertEquals(Colour::red(), $this->dataSource->get(2)->{Child::FAVOURITE_COLOUR});
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

        $action->run([IObjectAction::OBJECT_FIELD_NAME => 1]);
        $this->assertCount(4, $this->dataSource);

        $action->run([IObjectAction::OBJECT_FIELD_NAME => 5]);
        $this->assertCount(3, $this->dataSource);
    }

    public function testCustomObjectActionWithFilter()
    {
        $this->assertSame(true, $this->module->hasObjectAction('swap-names'));

        $action = $this->module->getObjectAction('swap-names');

        $this->assertSame('swap-names', $action->getName());
        $this->assertSame(Person::class, $action->getObjectType());
        $this->assertSame(null, $action->getReturnTypeClass());
        $this->assertEquals([Permission::named(ICrudModule::EDIT_PERMISSION)], array_values($action->getRequiredPermissions()));
        $this->assertEquals(
                $this->dataSource->matching(
                        $this->dataSource->criteria()->whereInstanceOf(Adult::class)
                ),
                array_values($action->getSupportedObjects($this->dataSource->getAll()))
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