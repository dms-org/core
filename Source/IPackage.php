<?php

namespace Iddigital\Cms\Core;

use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Module\IModule;

/**
 * The interface for a package.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IPackage
{
    /**
     * Gets the permissions.
     *
     * @return IPermission[]
     */
    public function getPermissions();

    /**
     * Gets the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the label.
     *
     * @return string
     */
    public function getLabel();
    
    /**
     * Gets the frontend API.
     * 
     * @return IFrontend
     */
    public function getFrontend();
    
    /**
     * Gets the modules.
     * 
     * @return IModule[]
     */
    public function getModules();
}
