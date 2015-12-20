<?php

namespace Dms\Core\Table;

use Dms\Core\Table\Chart\IChartDataSource;
use Dms\Core\Table\Criteria\RowCriteria;

/**
 * The table data source interface
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface ITableDataSource
{
    /**
     * Gets the table structure for data source.
     *
     * @return ITableStructure
     */
    public function getStructure();

    /**
     * Creates a new criteria for this data source.
     *
     * @return RowCriteria
     */
    public function criteria();

    /**
     * Loads the table sections according to the supplied row criteria
     * or all rows if null is passed.
     *
     * @param IRowCriteria|null $criteria
     *
     * @return IDataTable
     */
    public function load(IRowCriteria $criteria = null);

    /**
     * Returns the number of rows matching the supplied criteria
     * or all rows if null is passed.
     *
     * @param IRowCriteria|null $criteria
     *
     * @return int
     */
    public function count(IRowCriteria $criteria = null);

    /**
     * Creates a chart data source that will load the data
     * from this table.
     *
     * Example:
     * <code>
     * ->asChart(function (ChartTableMapperDefinition $map) {
     *      $map->structure(new LineChart(
     *              $map->column('some.table-component')->toAxis(),
     *              $map->column('some.other-component')->toAxis()
     *      ));
     * });
     * </code>
     *
     * @param callable $chartMappingCallback
     *
     * @return IChartDataSource
     */
    public function asChart(callable $chartMappingCallback);
}