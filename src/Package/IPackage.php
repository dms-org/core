<?php declare(strict_types = 1);

namespace Dms\Core\Package;

use Dms\Core\Auth\IPermission;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Ioc\IIocContainer;
use Dms\Core\Module\IModule;
use Dms\Core\Module\ModuleNotFoundException;
use Dms\Core\Util\Metadata\IMetadataProvider;

/**
 * The interface for a package.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IPackage extends IMetadataProvider
{
    /**
     * Gets the name.
     *
     * @return string
     */
    public function getName() : string;

    /**
     * Returns whether the package has a dashboard
     *
     * @return bool
     */
    public function hasDashboard() : bool;

    /**
     * Loads the dashboard for the package.
     *
     * NOTE: this will load the required modules for the dashboard.
     *
     * @return IDashboard
     * @throws InvalidOperationException
     */
    public function loadDashboard() : IDashboard;

    /**
     * Gets the names of the modules contained within this package.
     *
     * @return string[]
     */
    public function getModuleNames() : array;

    /**
     * Loads the modules.
     *
     * @return IModule[]
     */
    public function loadModules() : array;

    /**
     * Returns whether the package contains the supplied module.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasModule(string $name) : bool;

    /**
     * Loads the module with the supplied name.
     *
     * @param string $name
     *
     * @return IModule
     * @throws ModuleNotFoundException if the module name is invalid
     */
    public function loadModule(string $name) : IModule;

    /**
     * Loads the namespaced permissions.
     *
     * NOTE: this will load the modules.
     *
     * @return IPermission[]
     */
    public function loadPermissions() : array;

    /**
     * Gets the inversion of control container used within this package.
     *
     * @return IIocContainer
     */
    public function getIocContainer() : IIocContainer;
}
