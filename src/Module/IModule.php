<?php

namespace Dms\Core\Module;

use Dms\Core\Auth\IPermission;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Form;
use Dms\Core\Widget\IWidget;

/**
 * The API for a module.
 *
 * A module represents a collection of actions, permissions, tables, charts, widgets etc.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IModule
{
    /**
     * Gets the package name
     *
     * @return string|null
     */
    public function getPackageName();

    /**
     * Sets the package name
     *
     * @param string $packageName
     *
     * @return void
     * @throws InvalidOperationException if the package name has already been set.
     */
    public function setPackageName($packageName);

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
     * @throws ActionNotFoundException
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
     * @throws ActionNotFoundException
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
     * @throws ActionNotFoundException
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
     * @return ITableDisplay[]
     */
    public function getTables();

    /**
     * Gets the table data source with the supplied name.
     *
     * @param string $name
     *
     * @return ITableDisplay
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
     * @return IChartDisplay[]
     */
    public function getCharts();

    /**
     * Gets the chart data source with the supplied name.
     *
     * @param string $name
     *
     * @return IChartDisplay
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