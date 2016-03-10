<?php declare(strict_types = 1);

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
    public function setPackageName(string $packageName);

    /**
     * Gets the name
     *
     * @return string
     */
    public function getName() : string;

    /**
     * Gets all the permissions used within the module
     *
     * @return IPermission[]
     */
    public function getPermissions() : array;

    /**
     * Gets all the permissions required to access this module.
     *
     * @return IPermission[]
     */
    public function getRequiredPermissions() : array;

    /**
     * Returns an equivalent module without any required permissions.
     *
     * @return static
     */
    public function withoutRequiredPermissions();

    /**
     * Returns whether the currently authenticated user is authorized to
     * access this module
     *
     * @return bool
     */
    public function isAuthorized() : bool;

    /**
     * Gets the actions.
     *
     * @return IAction[]
     */
    public function getActions() : array;

    /**
     * Gets the action with the supplied name.
     *
     * @param string $name
     *
     * @return IAction
     * @throws ActionNotFoundException
     */
    public function getAction(string $name) : IAction;

    /**
     * Returns whether the module contains the supplied action.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasAction(string $name) : bool;

    /**
     * Gets the parameterized actions.
     *
     * @return IParameterizedAction[]
     */
    public function getParameterizedActions() : array;

    /**
     * Gets the parameterized action with the supplied name.
     *
     * @param string $name
     *
     * @return IParameterizedAction
     * @throws ActionNotFoundException
     */
    public function getParameterizedAction(string $name) : IParameterizedAction;

    /**
     * Returns whether the module contains the supplied parameterized action.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasParameterizedAction(string $name) : bool;

    /**
     * Gets the unparameterized actions.
     *
     * @return IUnparameterizedAction[]
     */
    public function getUnparameterizedActions() : array;

    /**
     * Gets the unparameterized action with the supplied name.
     *
     * @param string $name
     *
     * @return IUnparameterizedAction
     * @throws ActionNotFoundException
     */
    public function getUnparameterizedAction(string $name) : IUnparameterizedAction;

    /**
     * Returns whether the module contains the supplied unparameterized action.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasUnparameterizedAction(string $name) : bool;

    /**
     * Gets the table data sources.
     *
     * @return ITableDisplay[]
     */
    public function getTables() : array;

    /**
     * Gets the table data source with the supplied name.
     *
     * @param string $name
     *
     * @return ITableDisplay
     * @throws InvalidArgumentException
     */
    public function getTable(string $name) : ITableDisplay;

    /**
     * Returns whether the module contains the supplied table.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasTable(string $name) : bool;

    /**
     * Gets the chart data sources.
     *
     * @return IChartDisplay[]
     */
    public function getCharts() : array;

    /**
     * Gets the chart data source with the supplied name.
     *
     * @param string $name
     *
     * @return IChartDisplay
     * @throws InvalidArgumentException
     */
    public function getChart(string $name) : IChartDisplay;

    /**
     * Returns whether the module contains the supplied chart.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasChart(string $name) : bool;

    /**
     * Gets the widgets.
     *
     * @return IWidget[]
     */
    public function getWidgets() : array;

    /**
     * Gets the widget with the supplied name.
     *
     * @param string $name
     *
     * @return IWidget
     * @throws InvalidArgumentException
     */
    public function getWidget(string $name) : \Dms\Core\Widget\IWidget;

    /**
     * Returns whether the module contains the supplied widget.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasWidget(string $name) : bool;
}