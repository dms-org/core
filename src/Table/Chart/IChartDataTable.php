<?php declare(strict_types = 1);

namespace Dms\Core\Table\Chart;

/**
 * The chart data table interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IChartDataTable
{
    /**
     * @return IChartStructure
     */
    public function getStructure() : IChartStructure;

    /**
     * Gets the array of rows for the chart.
     *
     * Each row is an array with the values grouped by
     * its axis name and indexed by column name.
     *
     * Example:
     * <code>
     * [
     *      ['x' => ['x' => 1], 'y' => ['val' => 0, 'total' => 0]],
     *      ['x' => ['x' => 1], 'y' => ['val' => 3, 'total' => 3]],
     *      ['x' => ['x' => 1], 'y' => ['val' => 3, 'total' => 6]],
     * ]
     * </code>
     *
     * @return array[]
     */
    public function getRows() : array;
}