<?php

namespace Dms\Core\Tests\Module\Fixtures;

use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Module\Definition\ModuleDefinition;
use Dms\Core\Module\Module;
use Dms\Core\Module\Table\TableDisplay;
use Dms\Core\Table\Builder\Column;
use Dms\Core\Table\Chart\Criteria\ChartCriteria;
use Dms\Core\Table\Chart\DataSource\Definition\ChartTableMapperDefinition;
use Dms\Core\Table\Chart\Structure\LineChart;
use Dms\Core\Table\Criteria\RowCriteria;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ModuleWithWidgets extends Module
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
        $module->name('test-module-with-widgets');

        $module->action('module-action')
            ->handler(function () {

            });

        $module->table('data-table')
            ->fromArray([
                ['x' => 1],
                ['x' => 2],
                ['x' => 3],
            ])
            ->withColumns([
                Column::from(Field::name('x')->label('X-val')->int()),
            ])
            ->withoutViews();

        $module->chart('line-chart')
            ->fromTable('data-table')
            ->map(function (ChartTableMapperDefinition $map) {
                $map->structure(new LineChart(
                    $map->column('x')->toAxis(),
                    $map->column('x')->toAxis('y', 'Y-val')
                ));
            })
            ->withoutViews();

        $module->widget('table-widget.all')
            ->label('Table Widget #1')
            ->withTable('data-table')
            ->allRows();

        $module->widget('table-widget.with-criteria')
            ->label('Table Widget #2')
            ->withTable('data-table')
            ->matching(function (RowCriteria $criteria) {
                $criteria->where('x', '>', 1);
            });

        $module->widget('chart-widget.all')
            ->label('Chart Widget #1')
            ->withChart('line-chart')
            ->allData();

        $module->widget('chart-widget.with-criteria')
            ->label('Chart Widget #2')
            ->withChart('line-chart')
            ->matching(function (ChartCriteria $criteria) {
                $criteria->where('x', '>', 1);
                $criteria->where('y', '<', 3);
            });

        $module->widget('action-widget')
            ->label('Action Widget #1')
            ->withAction('module-action');

        $module->widget('form-data-widget')
            ->label('Form Data Widget #1')
            ->withFormData(Form::create()->section('Data', [
                Field::name('field')->label('Field')->string()->value('Some string'),
            ])->build());
    }
}