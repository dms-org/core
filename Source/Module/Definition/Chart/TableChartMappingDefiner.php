<?php

namespace Iddigital\Cms\Core\Module\Definition\Chart;

use Iddigital\Cms\Core\Table\ITableDataSource;

/**
 * The table-to-chart mapping definer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableChartMappingDefiner
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var ITableDataSource
     */
    private $tableSource;

    /**
     * @var callable
     */
    private $callback;

    /**
     * TableChartMappingDefiner constructor.
     *
     * @param string           $name
     * @param ITableDataSource $tableSource
     * @param callable         $callback
     */
    public function __construct($name, ITableDataSource $tableSource, callable $callback)
    {
        $this->name        = $name;
        $this->tableSource = $tableSource;
        $this->callback    = $callback;
    }

    /**
     * Defines the structure of the chart and the data to load from the
     * table data source.
     *
     * Example:
     * <code>
     * ->map(function (ChartTableMapperDefinition $map) {
     *      $map->structure(new LineChart(
     *              $map->column('some.table-component')->toAxis(),
     *              $map->column('some.other-component')->toAxis()
     *      ));
     * });
     * </code>
     *
     * @param callable $mapDefinitionCallback
     *
     * @return ChartViewsDefiner
     */
    public function map(callable $mapDefinitionCallback)
    {
        $chartDataSource = $this->tableSource->asChart($mapDefinitionCallback);

        return new ChartViewsDefiner($this->name, $this->callback, $chartDataSource);
    }
}