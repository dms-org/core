<?php

namespace Iddigital\Cms\Core\Common\Crud\Dream;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Common\Crud\Definition\Form\CrudFormDefinition;
use Iddigital\Cms\Core\Common\Crud\Definition\Table\SummaryTableDefinition;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Module\Definition\ModuleDefinition;
use Iddigital\Cms\Core\Table\Builder\Column;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CrudModuleDream extends CrudModule
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
    final protected function defineReadModule(ModuleDefinition $module)
    {
        $module->name('people');

        $module->labelObjects()->fromProperty('first_name');
        // Or
        $module->labelObjects()->fromCallback(function (Person $person) {
            return $person->getFullName();
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