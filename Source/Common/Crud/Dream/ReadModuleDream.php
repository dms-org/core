<?php

namespace Iddigital\Cms\Core\Common\Crud\Dream;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Common\Crud\ReadModule;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
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
    final protected function defineReadModule(ModuleDefinition $module)
    {
        $module->name('people');

        $module->actionOnObject('create-foo')
                ->authorize('create-foo.permission')
                ->handler(function (Person $person) {
                    $person->createFoo();
                    $this->repository->save($person);
                });

        $module->detailsForm(function (Person $person) {
            return Form::create()
                    ->section('Details', [
                            Field::name('email')->label('Email')->string()->email()->value($person->email)->uniqueIn($this->repository, 'email'),
                            Field::name('first_name')->label('First Name')->string()->value($person->firstName),
                            Field::name('last_name')->label('Last Name')->string()->value($person->lastName),
                            Field::name('age')->label('Age')->int()->value($person->age),
                    ]);
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