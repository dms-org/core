<?php

namespace Iddigital\Cms\Core\Module;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Table\Chart\IChartDataSource;
use Iddigital\Cms\Core\Table\ITableDataSource;
use Iddigital\Cms\Core\Widget\IWidget;

/**
 * The API for a module.
 *
 * A module represents is an abstraction over the API surrounding a given entity (aggregate root).
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IModule
{
    /**
     * Gets the name
     *
     * @return string
     */
    public function getName();

    /**
     * Gets all the permissions used within the module
     *
     * @return IPermission[]
     */
    public function getPermissions();

    /**
     * Gets the actions.
     *
     * @return IAction[]
     */
    public function getActions();

    /**
     * Gets the action with the supplied name.
     *
     * @param string $name
     *
     * @return IAction
     * @throws InvalidArgumentException
     */
    public function getAction($name);

    /**
     * Returns whether the module contains the supplied action.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasAction($name);

    /**
     * Gets the parameterized actions.
     *
     * @return IParameterizedAction[]
     */
    public function getParameterizedActions();

    /**
     * Gets the parameterized action with the supplied name.
     *
     * @param string $name
     *
     * @return IParameterizedAction
     * @throws InvalidArgumentException
     */
    public function getParameterizedAction($name);

    /**
     * Returns whether the module contains the supplied parameterized action.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasParameterizedAction($name);

    /**
     * Gets the unparameterized actions.
     *
     * @return IUnparameterizedAction[]
     */
    public function getUnparameterizedActions();

    /**
     * Gets the unparameterized action with the supplied name.
     *
     * @param string $name
     *
     * @return IUnparameterizedAction
     * @throws InvalidArgumentException
     */
    public function getUnparameterizedAction($name);

    /**
     * Returns whether the module contains the supplied unparameterized action.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasUnparameterizedAction($name);

    /**
     * Gets the table data sources.
     *
     * @return ITableDataSource[]
     */
    public function getTables();

    /**
     * Gets the table data source with the supplied name.
     *
     * @param string $name
     *
     * @return ITableDataSource
     * @throws InvalidArgumentException
     */
    public function getTable($name);

    /**
     * Returns whether the module contains the supplied table.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasTable($name);

    /**
     * Gets the chart data sources.
     *
     * @return IChartDataSource[]
     */
    public function getCharts();

    /**
     * Gets the chart data source with the supplied name.
     *
     * @param string $name
     *
     * @return IChartDataSource
     * @throws InvalidArgumentException
     */
    public function getChart($name);

    /**
     * Returns whether the module contains the supplied chart.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasChart($name);

    /**
     * Gets the widgets.
     *
     * @return IWidget[]
     */
    public function getWidgets();

    /**
     * Gets the widget with the supplied name.
     *
     * @param string $name
     *
     * @return IWidget
     * @throws InvalidArgumentException
     */
    public function getWidget($name);

    /**
     * Returns whether the module contains the supplied widget.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasWidget($name);
}