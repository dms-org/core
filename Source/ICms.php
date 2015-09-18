<?php

namespace Iddigital\Cms\Core;

/**
 * The API for a CMS.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface ICms
{
    /**
     * Get the loaded packages.
     * 
     * @return IPackage[]
     */
    public function getPackages();
    
    /**
     * Returns whether a package with the supplied name is installed.
     * 
     * @param string $name
     * @return bool
     */
    public function hasPackage($name);
    
    /**
     * Gets the package with the supplied name.
     * 
     * @param string $name
     *
     * @return IPackage
     * @throws PackageNotInstalledException If the package is not installed
     */
    public function getPackage($name);

    /**
     * Gets the authentication system for the cms.
     *
     * @return Auth\IAuthSystem
     */
    public function getAuth();

    /**
     * Gets the language provider for the cms.
     *
     * @return Language\ILanguageProvider
     */
    public function getLang();
}
