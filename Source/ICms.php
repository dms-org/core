<?php

namespace Iddigital\Cms\Core;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Language\ILanguageProvider;
use Iddigital\Cms\Core\Package\IPackage;
use Iddigital\Cms\Core\Package\PackageNotInstalledException;
use Interop\Container\ContainerInterface;

/**
 * The interface for a CMS.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface ICms
{
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
     * @throws PackageNotInstalledException If the package is not installed
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
