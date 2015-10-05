<?php

namespace Iddigital\Cms\Core\Module\Definition;

use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Module\IAction;
use Iddigital\Cms\Core\Table\ITableDataSource;

/**
 * The finalized module definition.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FinalizedModuleDefinition
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
     * @var ITableDataSource[]
     */
    private $tables;

    /**
     * FinalizedModuleDefinition constructor.
     *
     * @param string                   $name
     * @param IAction[]          $actions
     * @param ITableDataSource[] $tables
     */
    public function __construct($name, array $actions, array $tables)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'actions', $actions, IAction::class);
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'tables', $tables, ITableDataSource::class);

        $this->name = $name;
        $this->actions = $actions;
        $this->tables  = $tables;

        foreach ($actions as $action) {
            foreach ($action->getRequiredPermissions() as $permission) {
                $this->permissions[$permission->getName()] = $permission;
            }
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return IPermission[]
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @return IAction[]
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @return ITableDataSource[]
     */
    public function getTables()
    {
        return $this->tables;
    }
}