<?php

namespace Dms\Core\Common\Crud\Dream;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Common\Crud\Definition\Form\CrudFormDefinition;
use Dms\Core\Common\Crud\Definition\ReadModuleDefinition;
use Dms\Core\Common\Crud\Definition\Table\SummaryTableDefinition;
use Dms\Core\Common\Crud\ReadModule;
use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Model\Object\ArrayDataObject;
use Dms\Core\Table\Builder\Column;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModuleDream extends ReadModule
{
    /**
     * @var IPeopleRepository
     */
    protected $repository;

    /**
     * @inheritDoc
     */
    public function __construct(IPeopleRepository $repository, IAuthSystem $authSystem)
    {
        parent::__construct($repository, $authSystem);
    }

    /**
     * @inheritDoc
     */
    final protected function defineReadModule(ReadModuleDefinition $module)
    {
        $module->name('people');

        $module->labelObjects()->fromProperty('first_name');
        // Or
        $module->labelObjects()->fromCallback(function (Person $person) {
            return $person->getFullName();
        });

        $module->objectAction('create-foo')
                ->authorize('create-foo.permission')
                ->handler(function (Person $person) {
                    $person->createFoo();
                    $this->repository->save($person);
                });

        $module->objectAction('do-something-foo')
                ->authorize('do-foo.permission')
                ->form(Form::create())
                ->handler(function (Person $person, ArrayDataObject $data) {
                    $person->setData($data['abc']);
                    $this->repository->save($person);
                });

        $module->crudForm(function (CrudFormDefinition $form) {
            $form->section('Details', [
                //
                $form->field(Field::name('first_name')->label('First Name')->string()->required())
                        ->bindToProperty('firstName'),
                //
                $form->field(Field::name('last_name')->label('Last Name')->string()->required())
                        ->bindToProperty('lastName'),
                //
                $form->field(Field::name('age')->label('Age')->int()->required())
                        ->bindToProperty('age'),
            ]);

            $form->dependentOn(['age'], function (CrudFormDefinition $form, array $data) {
                if ($data['age'] > 50) {
                    $form->section('Retirement', [
                        //
                        $form->field(Field::name('pension')->label('Pension')->bool()->required())
                                ->bindToProperty('pension'),
                    ]);
                } else {
                    $form->section('Job', [
                        //
                        $form->field(Field::name('job')->label('Job')->string()->required())
                                ->bindToCallbacks(function (Person $person) {
                                    return $person->job;
                                }, function (Person $person, $job) {
                                    $person->job = $job;
                                }),
                        // Or
                        $form->field(Field::name('job')->label('Job')->string()->required())
                                ->bindToGetSetMethods('getJob', 'setJob'),
                    ]);
                }
            });

            $form->dependentOnObject(function (CrudFormDefinition $form, Person $person = null) {
                if ($person) {
                    $form->section('Retirement', [
                        //
                        $form->field(Field::name('pension')->label('Pension')->bool()->required())
                                ->bindToProperty('pension'),
                    ]);
                } else {
                    $form->section('Job', [
                        //
                        $form->field(Field::name('job')->label('Job')->string()->required())
                                ->bindToCallbacks(function (Person $person) {
                                    return $person->job;
                                }, function (Person $person, $job) {
                                    $person->job = $job;
                                }),
                        // Or
                        $form->field(Field::name('job')->label('Job')->string()->required())
                                ->bindToGetSetMethods('getJob', 'setJob'),
                    ]);
                }
            });
        });

        $module->summaryTable(function (SummaryTableDefinition $table) {
            $table->column(Column::name('name')->label('Name')->components([
                    Field::name('first_name')->label('First Name')->string(),
                    Field::name('last_name')->label('Last Name')->string(),
            ]));

            $table->mapProperty('firstName')->toComponent('name.first_name');
            $table->mapProperty('lastName')->toComponent('name.last_name');
            $table->mapProperty('age')->to(Field::name('age')->label('Age')->int());

            $table->view('default', 'Default')
                    ->asDefault()
                    ->loadAll()
                    ->orderByAsc(['product_name']);

            $table->view('category', 'Category')
                    ->loadAll()
                    ->groupBy('category.id')
                    ->orderByAsc(['category.name', 'category_sort_order'])
                    ->withReorder(function (Person $entity, $newOrderIndex) {
                        $this->repository->reorderPersonInCategory($entity, $newOrderIndex);
                    });
        });
    }
}