<?php

namespace Dms\Core\Tests\Module\Fixtures;

use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Module\Definition\Chart\ChartViewDefinition;
use Dms\Core\Module\Definition\ModuleDefinition;
use Dms\Core\Module\Module;
use Dms\Core\Table\Builder\Column;
use Dms\Core\Table\Chart\DataSource\Definition\ChartTableMapperDefinition;
use Dms\Core\Table\Chart\Structure\ChartAxis;
use Dms\Core\Table\Chart\Structure\LineChart;
use Dms\Core\Table\Chart\Structure\PieChart;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ModuleWithCharts extends Module
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
        $module->name('test-module-with-charts');

        $module->table('data-table')
            ->fromArray([
                ['x' => 1, 'y' => 2, 'y2' => 1],
                ['x' => 2, 'y' => 4, 'y2' => 4],
                ['x' => 3, 'y' => 6, 'y2' => 9],
            ])
            ->withColumns([
                Column::from(Field::name('x')->label('X-Val')->int()),
                Column::from(Field::name('y')->label('Y-Val')->int()),
                Column::from(Field::name('y2')->label('Y2-Val')->int()),
            ])
            ->withoutViews();

        $module->chart('line-chart')
                ->fromTable('data-table')
                ->map(function (ChartTableMapperDefinition $map) {
                    $map->structure(new LineChart(
                            $map->column('x')->toAxis(),
                            new ChartAxis('y', 'Y-Val', [
                                    $map->column('y')->asComponent(),
                                    $map->column('y2')->asComponent(),
                            ])
                    ));
                })
                ->withViews(function (ChartViewDefinition $view) {
                    $view->name('default', 'Default')
                            ->asDefault()
                            ->orderByAsc('x');

                    $view->name('reversed', 'Reversed')
                            ->orderByDesc('x');
                });;

        $module->chart('pie-chart')
                ->fromTable('data-table')
                ->map(function (ChartTableMapperDefinition $map) {
                    $map->structure(new PieChart(
                            $map->computed(
                                    function ($row) {
                                        return $row['x'] % 2 === 0;
                                    })
                                    ->requiresColumn('x')
                                    ->toAxis('is_even', 'Is Even', Field::forType()->bool()),
                            $map->column('y')->toAxis()
                    ));
                })
                ->withoutViews();
    }
}