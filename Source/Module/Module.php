<?php

namespace Iddigital\Cms\Core\Module;

use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Persistence\IRepository;

/**
 * The module base class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class Module implements IModule
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var IPermission[]
     */
    private $permissions = [];

    /**
     * @var IAction[]
     */
    private $actions;

    /**
     * @var IRepository
     */
    protected $repository;

    /**
     * Context constructor.
     *
     * @param string      $name
     * @param IAction[]   $actions
     * @param IRepository $repository
     */
    public function __construct($name, array $actions, IRepository $repository)
    {
        $this->name       = $name;
        $this->actions    = $actions;
        $this->repository = $repository;

        foreach ($this->actions as $action) {
            foreach ($action->getRequiredPermissions() as $permission) {
                $this->permissions[] = $permission;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    final public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * {@inheritDoc}
     */
    final public function getActions()
    {
        return $this->actions;
    }

    /**
     * {@inheritDoc}
     */
    final public function getRepository()
    {
        return $this->repository;
    }
}