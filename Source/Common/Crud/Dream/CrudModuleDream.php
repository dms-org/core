<?php

namespace Iddigital\Cms\Core\Common\Crud\Dream;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Common\Crud\ReadModule;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Module\Definition\ModuleDefinition;
use Iddigital\Cms\Core\Table\Builder\Column;
use Iddigital\Cms\Core\Table\DataSource\Definition\ObjectTableDefinition;

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

        $module->labelObject()->fromProperty('first_name');
        // Or
        $module->labelObject()->fromCallback(function (Person $person) {
            return $person->getFullName();
        });

        $module->crudForm(function (CrudFormDefinition $form, Person $person = null) {
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

            $form->dependentOn(['age'], function (CrudFormDefinition $form, array $data) use ($person) {
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
            }
        });

        $module->summaryTable(function (SummaryTableDefinition $map) {
            $map->column(Column::name('name')->label('Name')->components([
                    Field::name('first_name')->label('First Name')->string(),
                    Field::name('last_name')->label('Last Name')->string(),
            ]));

            $map->property('firstName')->toComponent('name.first_name');
            $map->property('lastName')->toComponent('name.last_name');
            $map->property('age')->to(Field::name('age')->label('Age')->int());

            $map->view('Default')
                    ->default()
                    ->orderByAsc(['product_name']);

            $map->view('Categories')
                    ->orderByAsc(['category.name', 'category_sort_order'])
                    ->groupBy('category.id')
                    ->withReorder(function (Person $entity, $newOrderIndex) {
                        $this->repository->reorderPersonInCategory($entity, $newOrderIndex);
                    });
        });
    }
}