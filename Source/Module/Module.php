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
     * @var IAuthSystem
     */
    protected $authSystem;

    /**
     * @var FinalizedModuleDefinition
     */
    protected $definition;

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
     * @var ITableDisplay[]
     */
    private $tables = [];

    /**
     * @var IChartDisplay[]
     */
    private $charts = [];

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
        $this->authSystem = $authSystem;

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
            $this->tables[$table->getName()] = $table;
        }

        foreach ($this->definition->getCharts() as $chart) {
            $this->charts[$chart->getName()] = $chart;
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
     * @return IAuthSystem
     */
    final public function getAuthSystem()
    {
        return $this->authSystem;
    }

    /**
     * {@inheritDoc}
     */
    final public function getName()
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
        return $this->tables;
    }

    /**
     * @inheritDoc
     */
    public function getTable($name)
    {
        if (isset($this->tables[$name])) {
            return $this->tables[$name];
        }

        throw InvalidArgumentException::format(
                'Invalid call to %s: unknown table name, expecting one of (%s), \'%s\' given',
                __METHOD__, Debug::formatValues(array_keys($this->tables)), $name
        );
    }

    /**
     * @inheritDoc
     */
    public function hasTable($name)
    {
        return isset($this->tables[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function getCharts()
    {
        return $this->charts;
    }

    /**
     * @inheritDoc
     */
    public function getChart($name)
    {
        if (isset($this->charts[$name])) {
            return $this->charts[$name];
        }

        throw InvalidArgumentException::format(
                'Invalid call to %s: unknown chart name, expecting one of (%s), \'%s\' given',
                __METHOD__, Debug::formatValues(array_keys($this->charts)), $name
        );
    }

    /**
     * @inheritDoc
     */
    public function hasChart($name)
    {
        return isset($this->charts[$name]);
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