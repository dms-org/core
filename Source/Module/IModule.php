<?php

namespace Iddigital\Cms\Core\Module;

use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Persistence\IRepository;
use Iddigital\Cms\Core\Table\ITableDataSource;

/**
 * The API for a module.
 *
 * A module represents is an abstraction over the API surrounding a given entity (aggregate root).
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IModule
{
    /**
     * Gets the name
     *
     * @return string
     */
    public function getName();

    /**
     * Gets all the permissions used within the module
     *
     * @return IPermission[]
     */
    public function getPermissions();

    /**
     * Gets the actions.
     *
     * @return IAction[]
     */
    public function getActions();

    /**
     * Gets the table data sources.
     *
     * @return ITableDataSource[]
     */
    public function getTables();
    
    /**
     * Gets the repository.
     * 
     * @return IRepository
     */
    public function getRepository();
}