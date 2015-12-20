<?php

namespace Dms\Core\Common\Crud\Dream;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Common\Crud\CrudModule;
use Dms\Core\Common\Crud\Definition\CrudModuleDefinition;
use Dms\Core\Common\Crud\Definition\Form\CrudFormDefinition;
use Dms\Core\Common\Crud\Definition\Table\SummaryTableDefinition;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Persistence\IRepository;
use Dms\Core\Table\Builder\Column;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CrudModuleDream extends CrudModule
{
    /**
     * @var IPeopleRepository|IRepository
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
    final protected function defineCrudModule(CrudModuleDefinition $module)
    {
        $module->name('people');

        $module->labelObjects()->fromProperty('first_name');
        // Or
        $module->labelObjects()->fromCallback(function (Person $person) {
            return $person->getFullName();
        });

        $module->objectAction('clone')
                ->authorize(self::EDIT_PERMISSION)
                ->where(function (Person $person) {
                    return $person->isCloneable();
                })
                ->handler(function (Person $person) {
                    $person->setId(null);
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

            if ($form->isDetailsForm()) {
                //
            }

            if ($form->isCreateForm()) {
                //
            }

            if ($form->isEditForm()) {
                //
                $form->onSubmit(function (Person $person, array $input) {
                    $person->foo = $input['data'];
                });
            }
        });

        $module->removeAction()
                ->afterRemove(function (Person $person) {
                    $this->deletePhotosOf($person);
                })
                ->deleteFromRepository();

        $module->summaryTable(function (SummaryTableDefinition $table) {
            $table->column(Column::name('name')->label('Name')->components([
                    Field::name('first_name')->label('First Name')->string(),
                    Field::name('last_name')->label('Last Name')->string(),
            ]));

            $table->mapProperty('firstName')->toComponent('name.first_name');
            $table->mapProperty('lastName')->toComponent('name.last_name');
            $table->mapProperty('age')->to(Field::name('age')->label('Age')->int());

            $table->mapProperty('categoryId')->toComponent('category.id');
            $table->mapProperty('load(categoryId).name')->toComponent('category.name');
            $table->mapProperty('loadAll(categoryIds).average(...)')->toComponent('...');
            $table->mapProperty('loadAll(categoryIds).average(...)')->toComponent('...');
            $table->mapProperty('friends.average(income)')->toComponent('...');
            $table->mapProperty('friends.flatten(friends)')->toComponent('...');
            $table->mapProperty('load(categoryId, App\Store\Category).')->toComponent('...');
            $table->mapProperty('loadAll(friends.flatten(relativesIds)).')->toComponent('...');
            $table->mapProperty('friends.average(income.months.sum(amount))')->toComponent('...');

            $table->view('default', 'Default')
                    ->asDefault()
                    ->loadAll()
                    ->orderByAsc(['product_name']);

            $table->view('categories', 'Categories')
                    ->loadAll()
                    ->orderByAsc(['category.name', 'category_sort_order'])
                    ->groupBy('category.id')
                    ->withReorder(function (Person $entity, $newOrderIndex) {
                        $this->repository->reorderPersonInCategory($entity, $newOrderIndex);
                    });
        });
    }
}