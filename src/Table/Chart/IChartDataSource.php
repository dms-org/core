<?php declare(strict_types = 1);

namespace Dms\Core\Table\Chart;

use Dms\Core\Table\Chart\Criteria\ChartCriteria;

/**
 * The chart data source interface
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IChartDataSource
{
    /**
     * Gets the chart structure for data source.
     *
     * @return IChartStructure
     */
    public function getStructure() : IChartStructure;

    /**
     * Creates a new criteria for this data source.
     *
     * @return ChartCriteria
     */
    public function criteria() : Criteria\ChartCriteria;

    /**
     * Loads the chart data according to the supplied criteria
     * or all the data if null is passed.
     *
     * @param IChartCriteria|null $criteria
     *
     * @return IChartDataTable
     */
    public function load(IChartCriteria $criteria = null) : IChartDataTable;
}