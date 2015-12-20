<?php

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
    public function getAxes();

    /**
     * Gets the axis with the supplied name.
     *
     * @param string $name
     *
     * @return IChartAxis
     * @throws InvalidArgumentException
     */
    public function getAxis($name);

    /**
     * Returns whether the chart has the supplied axis.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasAxis($name);
}