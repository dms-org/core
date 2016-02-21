<?php declare(strict_types = 1);

namespace Dms\Core\Package;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Module\IModule;
use Dms\Core\Module\ModuleNotFoundException;
use Dms\Core\Package\Definition\PackageDefinition;
use Dms\Core\Util\Debug;
use Interop\Container\ContainerInterface;

/**
 * The package base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Package implements IPackage
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string[]
     */
    protected $dashboardWidgetNames;

    /**
     * @var IDashboard
     */
    protected $dashboard;

    /**
     * @var string[]
     */
    protected $nameModuleClassMap;

    /**
     * @var IModule[]
     */
    protected $loadedModules = [];

    /**
     * The array of currently loading modules.
     *
     * This prevents infinite recursion when referencing other modules.
     *
     * @var array
     */
    protected $loadingModules = [];

    /**
     * Package constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $definition = new PackageDefinition();
        $this->define($definition);
        $finalizedDefinition = $definition->finalize();

        $this->name                 = $finalizedDefinition->getName();
        $this->dashboardWidgetNames = $finalizedDefinition->getDashboardWidgetNames();
        $this->nameModuleClassMap   = $finalizedDefinition->getNameModuleClassMap();
    }

    /**
     * Defines the structure of this cms package.
     *
     * @param PackageDefinition $package
     *
     * @return void
     */
    abstract protected function define(PackageDefinition $package);

    /**
     * @inheritDoc
     */
    final public function getName() : string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function hasDashboard() : bool
    {
        return count($this->dashboardWidgetNames) > 0;
    }

    /**
     * @inheritDoc
     */
    public function loadDashboard() : IDashboard
    {
        if (!$this->hasDashboard()) {
            throw InvalidOperationException::methodCall(__METHOD__, 'no dashboard widgets defined');
        }

        if (!$this->dashboard) {
            $this->dashboard = $this->loadDashboardWith($this->dashboardWidgetNames);
        }

        return $this->dashboard;
    }

    protected function loadDashboardWith(array $widgetNames)
    {
        $widgets = [];

        foreach ($widgetNames as $name) {
            if (strpos($name, '.') === false) {
                throw InvalidArgumentException::format(
                        'Invalid dashboard widget name: must be in format "module-name.widget-name", \'%s\' given',
                        $name
                );
            }

            $moduleName = substr($name, 0, strpos($name, '.'));
            $widgetName = substr($name, strpos($name, '.') + 1);

            $module    = $this->loadModule($moduleName);
            $widget    = $module->getWidget($widgetName);
            $widgets[] = new DashboardWidget($module, $widget);
        }

        return new Dashboard($widgets);
    }

    /**
     * @inheritDoc
     */
    final public function getModuleNames() : array
    {
        return array_keys($this->nameModuleClassMap);
    }

    /**
     * @inheritDoc
     */
    final public function loadModule(string $name) : IModule
    {

        if (!isset($this->nameModuleClassMap[$name])) {
            throw ModuleNotFoundException::format(
                    'Invalid module name supplied to %s: expecting one of (%s), \'%s\' given',
                    get_class($this) . '::' . __FUNCTION__, Debug::formatValues($this->getModuleNames()), $name
            );
        }

        if (!isset($this->loadedModules[$name])) {
            $moduleClass = $this->nameModuleClassMap[$name];

            $this->loadedModules[$name] = $this->loadModuleFromClass($name, $moduleClass);
        }

        return $this->loadedModules[$name];
    }

    /**
     * @param string $name
     * @param string $moduleClass
     *
     * @return IModule
     * @throws InvalidArgumentException
     */
    private function loadModuleFromClass(string $name, string $moduleClass) : IModule
    {
        if (!is_subclass_of($moduleClass, IModule::class, true)) {
            throw InvalidArgumentException::format(
                    'Invalid module class defined within package \'%s\': expecting subclass of %s, %s given',
                    $this->name, IModule::class, $moduleClass
            );
        }

        $this->loadingModules[$name] = true;

        /** @var IModule $module */
        $module = $this->container->get($moduleClass);

        unset($this->loadingModules[$name]);

        if ($module->getName() !== $name) {
            throw InvalidArgumentException::format(
                    'Invalid module class defined within package \'%s\': defined module name \'%s\' does not match module name \'%s\' from instance of %s',
                    $this->name, $name, $module->getName(), get_class($module)
            );
        }

        $module->setPackageName($this->name);

        return $module;
    }

    /**
     * @inheritDoc
     */
    final public function loadModules() : array
    {
        foreach ($this->nameModuleClassMap as $name => $moduleClass) {
            if (!isset($this->loadingModules[$name])) {
                $this->loadModule($name);
            }
        }

        return $this->loadedModules;
    }

    /**
     * @inheritDoc
     */
    final public function hasModule(string $name) : bool
    {
        return isset($this->nameModuleClassMap[$name]);
    }

    /**
     * @inheritDoc
     */
    final public function loadPermissions() : array
    {
        $namespacedPermissions = [];

        foreach ($this->loadModules() as $moduleName => $module) {
            foreach ($module->getPermissions() as $permission) {
                $namespacedPermissions[] = $permission;
            }
        }

        return $namespacedPermissions;
    }

    /**
     * @inheritDoc
     */
    final public function getIocContainer() : ContainerInterface
    {
        return $this->container;
    }
}