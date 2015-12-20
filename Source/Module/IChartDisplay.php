<?php

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
    public function getName();

    /**
     * Gets the chart data source
     *
     * @return IChartDataSource
     */
    public function getDataSource();

    /**
     * Gets the default view.
     *
     * @return IChartView
     */
    public function getDefaultView();

    /**
     * Gets the views.
     *
     * @return IChartView[]
     */
    public function getViews();

    /**
     * Get whether the view with the supplied name exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasView($name);

    /**
     * Gets the view with the supplied name.
     *
     * @param string $name
     *
     * @return IChartView
     * @throws InvalidArgumentException
     */
    public function getView($name);
}