<?php

namespace Iddigital\Cms\Core\Package;

use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Module\IModule;
use Interop\Container\ContainerInterface;

/**
 * The interface for a package.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IPackage
{
    /**
     * Gets the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the names of the modules contained within this package.
     *
     * @return string[]
     */
    public function getModuleNames();

    /**
     * Loads the modules.
     *
     * @return IModule[]
     */
    public function loadModules();

    /**
     * Returns whether the package contains the supplied module.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasModule($name);

    /**
     * Loads the module with the supplied name.
     *
     * @param string $name
     *
     * @return IModule
     * @throws InvalidArgumentException if the module name is invalid
     */
    public function loadModule($name);

    /**
     * Loads the namespaced permissions.
     *
     * NOTE: this will load the modules.
     *
     * @return IPermission[]
     */
    public function loadPermissions();

    /**
     * Gets the inversion of control container used within this package.
     *
     * @return ContainerInterface
     */
    public function getIocContainer();
}
