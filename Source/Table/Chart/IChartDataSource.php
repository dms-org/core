<?php

namespace Iddigital\Cms\Core\Table\Chart;

use Iddigital\Cms\Core\Table\Chart\Criteria\ChartCriteria;

/**
 * The chart data source interface
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IChartDataSource
{
    /**
     * Gets the name of the data source.
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the chart structure for data source.
     *
     * @return IChartStructure
     */
    public function getStructure();

    /**
     * Creates a new criteria for this data source.
     *
     * @return ChartCriteria
     */
    public function criteria();

    /**
     * Loads the chart data according to the supplied criteria
     * or all the data if null is passed.
     *
     * @param IChartCriteria|null $criteria
     *
     * @return IChartDataTable
     */
    public function load(IChartCriteria $criteria = null);
}