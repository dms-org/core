<?php

namespace Iddigital\Cms\Core\Common\Crud\Dream;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Common\Crud\Definition\ReadModuleDefinition;
use Iddigital\Cms\Core\Common\Crud\ReadModule;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Model\Object\ArrayDataObject;
use Iddigital\Cms\Core\Module\Definition\ModuleDefinition;
use Iddigital\Cms\Core\Table\Builder\Column;
use Iddigital\Cms\Core\Table\DataSource\Definition\ObjectTableDefinition;

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
        });

        $module->summaryTable(function (ObjectTableDefinition $map) {
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
                    ->withSortColumnPropertyAs('categorySortOrder');
        });
    }
}