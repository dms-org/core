<?php declare(strict_types = 1);

namespace Dms\Core;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IPermission;
use Dms\Core\Event\IEventDispatcher;
use Dms\Core\Ioc\IIocContainer;
use Dms\Core\Language\ILanguageProvider;
use Dms\Core\Package\IPackage;
use Dms\Core\Package\PackageNotFoundException;
use Psr\Cache\CacheItemPoolInterface;

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
    public function getPackageNames() : array;

    /**
     * Loads the installed packages.
     *
     * @return IPackage[]
     */
    public function loadPackages() : array;

    /**
     * Returns whether a package with the supplied name is installed.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasPackage(string $name) : bool;

    /**
     * Loads the package with the supplied name.
     *
     * @param string $name
     *
     * @return IPackage
     * @throws PackageNotFoundException If the package is not installed
     */
    public function loadPackage(string $name) : IPackage;

    /**
     * Gets the authentication system for the cms.
     *
     * @return IAuthSystem
     */
    public function getAuth() : IAuthSystem;

    /**
     * Gets the language provider for the cms.
     *
     * @return ILanguageProvider
     */
    public function getLang() : ILanguageProvider;

    /**
     * Gets the cache for the cms.
     *
     * @return CacheItemPoolInterface
     */
    public function getCache() : CacheItemPoolInterface;

    /**
     * Gets the event dispatcher used within the cms instance.
     *
     * @return IEventDispatcher
     */
    public function getEventDispatcher() : IEventDispatcher;

    /**
     * Gets the inversion of control container used within this cms instance.
     *
     * @return IIocContainer
     */
    public function getIocContainer() : IIocContainer;

    /**
     * Loads the namespaced permissions.
     *
     * NOTE: this will load all the packages and all the modules.
     *
     * @return IPermission[]
     */
    public function loadPermissions() : array;
}
