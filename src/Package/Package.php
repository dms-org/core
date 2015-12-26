<?php

namespace Dms\Core\Package;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\IModule;
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
    protected $nameModuleClassMap;

    /**
     * @var IModule[]
     */
    protected $loadedModules = [];

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

        $this->name               = $finalizedDefinition->getName();
        $this->nameModuleClassMap = $finalizedDefinition->getNameModuleClassMap();
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
    final public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    final public function getModuleNames()
    {
        return array_keys($this->nameModuleClassMap);
    }

    /**
     * @inheritDoc
     */
    final public function loadModule($name)
    {
        if (!isset($this->nameModuleClassMap[$name])) {
            throw InvalidArgumentException::format(
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
    private function loadModuleFromClass($name, $moduleClass)
    {
        if (!is_subclass_of($moduleClass, IModule::class, true)) {
            throw InvalidArgumentException::format(
                    'Invalid module class defined within package \'%s\': expecting subclass of %s, %s given',
                    $this->name, IModule::class, $moduleClass
            );
        }

        /** @var IModule $module */
        $module = $this->container->get($moduleClass);

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
    final public function loadModules()
    {
        foreach ($this->nameModuleClassMap as $name => $moduleClass) {
            $this->loadModule($name);
        }

        return $this->loadedModules;
    }

    /**
     * @inheritDoc
     */
    final public function hasModule($name)
    {
        return isset($this->nameModuleClassMap[$name]);
    }

    /**
     * @inheritDoc
     */
    final public function loadPermissions()
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
    final public function getIocContainer()
    {
        return $this->container;
    }
}