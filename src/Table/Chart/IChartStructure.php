<?php declare(strict_types = 1);

namespace Dms\Core\Table\Chart;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Table\ITableDataSource;

/**
 * The chart structure interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IChartStructure
{
    /**
     * Gets the axes in the chart.
     *
     * @return IChartAxis[]
     */
    public function getAxes() : array;

    /**
     * Gets the axis with the supplied name.
     *
     * @param string $name
     *
     * @return IChartAxis
     * @throws InvalidArgumentException
     */
    public function getAxis(string $name) : IChartAxis;

    /**
     * Returns whether the chart has the supplied axis.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasAxis(string $name) : bool;
}