<?php declare(strict_types = 1);

namespace Dms\Core\Module;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Table\Chart\IChartDataSource;

/**
 * The chart display interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IChartDisplay
{
    /**
     * Gets the name
     *
     * @return string
     */
    public function getName() : string;

    /**
     * Gets the chart data source
     *
     * @return IChartDataSource
     */
    public function getDataSource() : \Dms\Core\Table\Chart\IChartDataSource;

    /**
     * Gets the default view.
     *
     * @return IChartView
     */
    public function getDefaultView() : IChartView;

    /**
     * Gets the views.
     *
     * @return IChartView[]
     */
    public function getViews() : array;

    /**
     * Get whether the view with the supplied name exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasView(string $name) : bool;

    /**
     * Gets the view with the supplied name.
     *
     * @param string $name
     *
     * @return IChartView
     * @throws InvalidArgumentException
     */
    public function getView(string $name) : IChartView;
}