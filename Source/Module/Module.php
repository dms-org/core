<?php

namespace Iddigital\Cms\Core\Module;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Module\Definition\FinalizedModuleDefinition;
use Iddigital\Cms\Core\Module\Definition\ModuleDefinition;
use Iddigital\Cms\Core\Table\Chart\IChartDataSource;
use Iddigital\Cms\Core\Table\ITableDataSource;
use Iddigital\Cms\Core\Util\Debug;
use Iddigital\Cms\Core\Widget\IWidget;

/**
 * The module base class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
abstract class Module implements IModule
{
    /**
     * @var FinalizedModuleDefinition
     */
    private $definition;

    /**
     * @var IPermission[]
     */
    private $permissions = [];

    /**
     * @var IParameterizedAction[]
     */
    private $parameterizedActions = [];

    /**
     * @var IUnparameterizedAction[]
     */
    private $unparameterizedActions = [];

    /**
     * @var ITableDataSource[]
     */
    private $tableDataSources = [];

    /**
     * @var IChartDataSource[]
     */
    private $chartDataSources = [];

    /**
     * @var IWidget[]
     */
    private $widgets = [];

    /**
     * Module constructor.
     *
     * @param IAuthSystem $authSystem
     *
     * @throws InvalidArgumentException
     * @throws InvalidOperationException
     */
    public function __construct(IAuthSystem $authSystem)
    {
        $definition = new ModuleDefinition($authSystem);
        $this->define($definition);
        $this->definition = $definition->finalize();

        foreach ($this->definition->getPermissions() as $permission) {
            $this->permissions[$permission->getName()] = $permission;
        }

        foreach ($this->definition->getActions() as $action) {
            if ($action instanceof IParameterizedAction) {
                $this->parameterizedActions[$action->getName()] = $action;
            } elseif ($action instanceof IUnparameterizedAction) {
                $this->unparameterizedActions[$action->getName()] = $action;
            } else {
                throw InvalidArgumentException::format('Unknown action type: %s', Debug::getType($action));
            }
        }

        foreach ($this->definition->getTables() as $table) {
            $this->tableDataSources[$table->getName()] = $table;
        }

        foreach ($this->definition->getCharts() as $chart) {
            $this->chartDataSources[$chart->getName()] = $chart;
        }

        foreach ($this->definition->getWidgets() as $widget) {
            $this->widgets[$widget->getName()] = $widget;
        }
    }

    /**
     * Defines the module.
     *
     * @param ModuleDefinition $module
     *
     * @return void
     */
    abstract protected function define(ModuleDefinition $module);

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->definition->getName();
    }

    /**
     * {@inheritDoc}
     */
    final public function getPermissions()
    {
        return $this->definition->getPermissions();
    }

    /**
     * {@inheritDoc}
     */
    final public function getActions()
    {
        return $this->definition->getActions();
    }

    /**
     * @inheritDoc
     */
    public function getAction($name)
    {
        if (isset($this->unparameterizedActions[$name])) {
            return $this->unparameterizedActions[$name];
        } elseif (isset($this->parameterizedActions[$name])) {
            return $this->parameterizedActions[$name];
        }

        throw InvalidArgumentException::format(
                'Invalid call to %s: unknown action name, expecting one of (%s), \'%s\' given',
                __METHOD__, Debug::formatValues(array_keys($this->parameterizedActions + $this->unparameterizedActions)), $name
        );
    }

    /**
     * @inheritDoc
     */
    public function hasAction($name)
    {
        return isset($this->parameterizedActions[$name]) || isset($this->unparameterizedActions[$name]);
    }

    /**
     * @inheritDoc
     */
    public function getParameterizedActions()
    {
        return $this->parameterizedActions;
    }

    /**
     * @inheritDoc
     */
    public function getParameterizedAction($name)
    {
        if (isset($this->parameterizedActions[$name])) {
            return $this->parameterizedActions[$name];
        }

        throw InvalidArgumentException::format(
                'Invalid call to %s: unknown action name, expecting one of (%s), \'%s\' given',
                __METHOD__, Debug::formatValues(array_keys($this->parameterizedActions)), $name
        );
    }

    /**
     * @inheritDoc
     */
    public function hasParameterizedAction($name)
    {
        return isset($this->parameterizedActions[$name]);
    }

    /**
     * @inheritDoc
     */
    public function getUnparameterizedActions()
    {
        return $this->unparameterizedActions;
    }

    /**
     * @inheritDoc
     */
    public function getUnparameterizedAction($name)
    {
        if (isset($this->unparameterizedActions[$name])) {
            return $this->unparameterizedActions[$name];
        }

        throw InvalidArgumentException::format(
                'Invalid call to %s: unknown action name, expecting one of (%s), \'%s\' given',
                __METHOD__, Debug::formatValues(array_keys($this->unparameterizedActions)), $name
        );
    }

    /**
     * @inheritDoc
     */
    public function hasUnparameterizedAction($name)
    {
        return isset($this->unparameterizedActions[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function getTables()
    {
        return $this->tableDataSources;
    }

    /**
     * @inheritDoc
     */
    public function getTable($name)
    {
        if (isset($this->tableDataSources[$name])) {
            return $this->tableDataSources[$name];
        }

        throw InvalidArgumentException::format(
                'Invalid call to %s: unknown table name, expecting one of (%s), \'%s\' given',
                __METHOD__, Debug::formatValues(array_keys($this->tableDataSources)), $name
        );
    }

    /**
     * @inheritDoc
     */
    public function hasTable($name)
    {
        return isset($this->tableDataSources[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function getCharts()
    {
        return $this->chartDataSources;
    }

    /**
     * @inheritDoc
     */
    public function getChart($name)
    {
        if (isset($this->chartDataSources[$name])) {
            return $this->chartDataSources[$name];
        }

        throw InvalidArgumentException::format(
                'Invalid call to %s: unknown chart name, expecting one of (%s), \'%s\' given',
                __METHOD__, Debug::formatValues(array_keys($this->chartDataSources)), $name
        );
    }

    /**
     * @inheritDoc
     */
    public function hasChart($name)
    {
        return isset($this->chartDataSources[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * @inheritDoc
     */
    public function getWidget($name)
    {
        if (isset($this->widgets[$name])) {
            return $this->widgets[$name];
        }

        throw InvalidArgumentException::format(
                'Invalid call to %s: unknown widget name, expecting one of (%s), \'%s\' given',
                __METHOD__, Debug::formatValues(array_keys($this->widgets)), $name
        );
    }

    /**
     * @inheritDoc
     */
    public function hasWidget($name)
    {
        return isset($this->widgets[$name]);
    }
}