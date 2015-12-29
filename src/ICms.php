<?php

namespace Dms\Core;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IPermission;
use Dms\Core\Language\ILanguageProvider;
use Dms\Core\Package\IPackage;
use Dms\Core\Package\PackageNotFoundException;
use Interop\Container\ContainerInterface;

/**
 * The interface for a CMS.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface ICms
{
    const VERSION = '0.1.0-dev';

    /**
     * Get the names of the installed packages within this cms.
     *
     * @return string[]
     */
    public function getPackageNames();

    /**
     * Loads the installed packages.
     *
     * @return IPackage[]
     */
    public function loadPackages();
    
    /**
     * Returns whether a package with the supplied name is installed.
     * 
     * @param string $name
     * @return bool
     */
    public function hasPackage($name);
    
    /**
     * Loads the package with the supplied name.
     * 
     * @param string $name
     *
     * @return IPackage
     * @throws PackageNotFoundException If the package is not installed
     */
    public function loadPackage($name);

    /**
     * Gets the authentication system for the cms.
     *
     * @return IAuthSystem
     */
    public function getAuth();

    /**
     * Gets the language provider for the cms.
     *
     * @return ILanguageProvider
     */
    public function getLang();

    /**
     * Gets the inversion of control container used within this cms instance.
     *
     * @return ContainerInterface
     */
    public function getIocContainer();

    /**
     * Loads the namespaced permissions.
     *
     * NOTE: this will load all the packages and all the modules.
     *
     * @return IPermission[]
     */
    public function loadPermissions();
}
