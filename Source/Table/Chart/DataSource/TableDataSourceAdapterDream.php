<?php

namespace Dms\Core\Table\Chart\DataSource;

use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Table\Chart\DataSource\Definition\ChartTableMapperDefinition;
use Dms\Core\Table\Chart\Structure\ChartAxis;
use Dms\Core\Table\Chart\Structure\LineChart;
use Dms\Core\Table\Chart\Structure\PieChart;
use Dms\Core\Table\Data\TableRow;

/**
 * The table data source adapter class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableDataSourceAdapterDream extends ChartTableDataSourceAdapter
{
    protected function define(ChartTableMapperDefinition $map)
    {
        $map->structure(new PieChart(
                $map->column('category.name')->toAxis(),
                $map->column('category.products_amount')->toAxis()
        ));

        $map->structure(new LineChart(
                $map->column('stats.date')->toAxis(),
                new ChartAxis('earnings', 'Earnings', [
                        $map->column('sales')->asComponent(),
                        $map->column('expenses')->asComponent(),
                        $map->computed(function (TableRow $row) {
                            return $row['sales'] - $row['expenses'];
                        })->asComponent('net_sales', 'Net Sales', Field::element()->decimal()),
                ])
        ));
    }
}