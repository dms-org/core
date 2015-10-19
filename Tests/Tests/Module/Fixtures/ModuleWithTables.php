<?php

namespace Iddigital\Cms\Core\Tests\Module\Fixtures;

use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Module\Definition\ModuleDefinition;
use Iddigital\Cms\Core\Module\Definition\Table\TableViewDefinition;
use Iddigital\Cms\Core\Module\Module;
use Iddigital\Cms\Core\Table\Builder\Column;
use Iddigital\Cms\Core\Table\DataSource\Definition\ObjectTableDefinition;
use Iddigital\Cms\Core\Table\ITableDataSource;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ModuleWithTables extends Module
{

    /**
     * Defines the module.
     *
     * @param ModuleDefinition $module
     *
     * @return void
     */
    protected function define(ModuleDefinition $module)
    {
        $module->name('test-module-with-tables');

        $module->table('array-table')
                ->fromArray([
                        ['col' => 'a'],
                        ['col' => 'b'],
                        ['col' => 'c'],
                ])
                ->withColumns([
                        Column::from(Field::name('col')->label('Column')->string())
                ])
                ->withoutViews();


        $module->table('object-table')
                ->fromObjects(TestEntity::collection([
                        new TestEntity(1, 'Foo'),
                        new TestEntity(2, 'Bar'),
                        new TestEntity(3, 'Baz'),
                ]))
                ->withStructure(function (ObjectTableDefinition $map) {
                    $map->property('id')->to(Field::name('id')->label('Id')->int());
                    $map->property('name')->to(Field::name('name')->label('Name')->string());
                })
                ->withViews(function (TableViewDefinition $view) {
                    $view->name('default', 'Default')
                            ->asDefault()
                            ->loadAll();

                    $view->name('ordered', 'Ordered')
                            ->loadAll()
                            ->orderByAsc('name');
                });
    }
}