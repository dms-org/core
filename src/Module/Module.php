<?php declare(strict_types = 1);

namespace Dms\Core\Module;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\Permission;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Form;
use Dms\Core\Module\Definition\FinalizedModuleDefinition;
use Dms\Core\Module\Definition\ModuleDefinition;
use Dms\Core\Util\Debug;
use Dms\Core\Widget\IWidget;

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
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $packageName;

    /**
     * @var FinalizedModuleDefinition
     */
    protected $definition;

    /**
     * @var IPermission[]
     */
    private $requiredPermissions = [];

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

        $this->name                = $this->definition->getName();
        $this->requiredPermissions = Permission::namespaceAll($this->definition->getRequiredPermissions(), $this->name);
        $this->permissions         = Permission::namespaceAll($this->definition->getPermissions(), $this->name);

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
    final public function getAuthSystem() : IAuthSystem
    {
        return $this->authSystem;
    }

    /**
     * {@inheritDoc}
     */
    final public function getName() : string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getPackageName()
    {
        return $this->packageName;
    }

    /**
     * @inheritDoc
     */
    public function setPackageName(string $packageName)
    {
        if ($this->packageName) {
            throw InvalidOperationException::methodCall(__METHOD__, 'package name has already been set');
        }

        $this->packageName = $packageName;

        $this->requiredPermissions = Permission::namespaceAll($this->requiredPermissions, $packageName);
        $this->permissions         = Permission::namespaceAll($this->permissions, $packageName);

        foreach ($this->getActions() as $action) {
            $action->setPackageAndModuleName($packageName, $this->name);
        }

        foreach ($this->getWidgets() as $widget) {
            $widget->setPackageAndModuleName($packageName, $this->name);
        }
    }

    /**
     * {@inheritDoc}
     */
    final public function getPermissions() : array
    {
        return $this->permissions;
    }

    /**
     * @inheritDoc
     */
    final public function getRequiredPermissions() : array
    {
        return $this->requiredPermissions;
    }

    /**
     * {@inheritDoc}
     */
    final public function getActions() : array
    {
        return $this->definition->getActions();
    }

    /**
     * @inheritDoc
     */
    final public function isAuthorized() : bool
    {
        return $this->authSystem->isAuthorized($this->requiredPermissions);
    }

    /**
     * @inheritDoc
     */
    final public function getAction(string $name) : IAction
    {
        if (isset($this->unparameterizedActions[$name])) {
            return $this->unparameterizedActions[$name];
        } elseif (isset($this->parameterizedActions[$name])) {
            return $this->parameterizedActions[$name];
        }

        throw ActionNotFoundException::format(
            'Invalid call to %s: unknown action name, expecting one of (%s), \'%s\' given',
            __METHOD__, Debug::formatValues(array_keys($this->parameterizedActions + $this->unparameterizedActions)), $name
        );
    }

    /**
     * @inheritDoc
     */
    final public function hasAction(string $name) : bool
    {
        return isset($this->parameterizedActions[$name]) || isset($this->unparameterizedActions[$name]);
    }

    /**
     * @inheritDoc
     */
    final public function getParameterizedActions() : array
    {
        return $this->parameterizedActions;
    }

    /**
     * @inheritDoc
     */
    final public function getParameterizedAction(string $name) : IParameterizedAction
    {
        if (isset($this->parameterizedActions[$name])) {
            return $this->parameterizedActions[$name];
        }

        throw ActionNotFoundException::format(
            'Invalid call to %s: unknown action name, expecting one of (%s), \'%s\' given',
            __METHOD__, Debug::formatValues(array_keys($this->parameterizedActions)), $name
        );
    }

    /**
     * @inheritDoc
     */
    final public function hasParameterizedAction(string $name) : bool
    {
        return isset($this->parameterizedActions[$name]);
    }

    /**
     * @inheritDoc
     */
    final public function getUnparameterizedActions() : array
    {
        return $this->unparameterizedActions;
    }

    /**
     * @inheritDoc
     */
    final public function getUnparameterizedAction(string $name) : IUnparameterizedAction
    {
        if (isset($this->unparameterizedActions[$name])) {
            return $this->unparameterizedActions[$name];
        }

        throw ActionNotFoundException::format(
            'Invalid call to %s: unknown action name, expecting one of (%s), \'%s\' given',
            __METHOD__, Debug::formatValues(array_keys($this->unparameterizedActions)), $name
        );
    }

    /**
     * @inheritDoc
     */
    final public function hasUnparameterizedAction(string $name) : bool
    {
        return isset($this->unparameterizedActions[$name]);
    }

    /**
     * {@inheritDoc}
     */
    final public function getTables() : array
    {
        return $this->tables;
    }

    /**
     * @inheritDoc
     */
    final public function getTable(string $name) : ITableDisplay
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
    final public function hasTable(string $name) : bool
    {
        return isset($this->tables[$name]);
    }

    /**
     * {@inheritDoc}
     */
    final public function getCharts() : array
    {
        return $this->charts;
    }

    /**
     * @inheritDoc
     */
    final public function getChart(string $name) : IChartDisplay
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
    final public function hasChart(string $name) : bool
    {
        return isset($this->charts[$name]);
    }

    /**
     * {@inheritDoc}
     */
    final public function getWidgets() : array
    {
        return $this->widgets;
    }

    /**
     * @inheritDoc
     */
    final public function getWidget(string $name) : \Dms\Core\Widget\IWidget
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
    final public function hasWidget(string $name) : bool
    {
        return isset($this->widgets[$name]);
    }
}