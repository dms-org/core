<?php

namespace Iddigital\Cms\Core\Module;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Module\Definition\FinalizedModuleDefinition;
use Iddigital\Cms\Core\Module\Definition\ModuleDefinition;
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
        $this->authSystem = $authSystem;

        $definition         = new ModuleDefinition($authSystem);
        $overrideDefinition = $this->define($definition);

        if ($overrideDefinition) {
            $this->definition = $overrideDefinition;
        } else {

            $this->definition = $definition->finalize();
        }

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
    final public function getAction($name)
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
    final public function hasAction($name)
    {
        return isset($this->parameterizedActions[$name]) || isset($this->unparameterizedActions[$name]);
    }

    /**
     * @inheritDoc
     */
    final public function getParameterizedActions()
    {
        return $this->parameterizedActions;
    }

    /**
     * @inheritDoc
     */
    final public function getParameterizedAction($name)
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
    final public function hasParameterizedAction($name)
    {
        return isset($this->parameterizedActions[$name]);
    }

    /**
     * @inheritDoc
     */
    final public function getUnparameterizedActions()
    {
        return $this->unparameterizedActions;
    }

    /**
     * @inheritDoc
     */
    final public function getUnparameterizedAction($name)
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
    final public function hasUnparameterizedAction($name)
    {
        return isset($this->unparameterizedActions[$name]);
    }

    /**
     * {@inheritDoc}
     */
    final public function getTables()
    {
        return $this->tables;
    }

    /**
     * @inheritDoc
     */
    final public function getTable($name)
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
    final public function hasTable($name)
    {
        return isset($this->tables[$name]);
    }

    /**
     * {@inheritDoc}
     */
    final public function getCharts()
    {
        return $this->charts;
    }

    /**
     * @inheritDoc
     */
    final public function getChart($name)
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
    final public function hasChart($name)
    {
        return isset($this->charts[$name]);
    }

    /**
     * {@inheritDoc}
     */
    final public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * @inheritDoc
     */
    final public function getWidget($name)
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
    final public function hasWidget($name)
    {
        return isset($this->widgets[$name]);
    }
}