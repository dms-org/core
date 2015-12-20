<?php

namespace Dms\Core\Tests\Module\Fixtures;

use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Module\Definition\ModuleDefinition;
use Dms\Core\Module\Definition\Table\TableViewDefinition;
use Dms\Core\Module\Module;
use Dms\Core\Table\Builder\Column;
use Dms\Core\Table\DataSource\Definition\ObjectTableDefinition;
use Dms\Core\Table\ITableDataSource;

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