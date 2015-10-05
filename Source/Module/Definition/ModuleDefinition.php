<?php

namespace Iddigital\Cms\Core\Module\Definition;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Module\Definition\Table\TableDefiner;
use Iddigital\Cms\Core\Module\IAction;
use Iddigital\Cms\Core\Table\ITableDataSource;

/**
 * The module definition class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ModuleDefinition
{
    /**
     * @var IAuthSystem
     */
    private $authSystem;

    /**
     * @var IAction[]
     */
    private $actions = [];

    /**
     * @var IPermission[]
     */
    private $permissions = [];

    /**
     * @var ITableDataSource[]
     */
    private $tables = [];

    /**
     * ModuleDefinition constructor.
     *
     * @param IAuthSystem $authSystem
     */
    public function __construct(IAuthSystem $authSystem)
    {
        $this->authSystem = $authSystem;
    }

    /**
     * Defines an action with the supplied name.
     *
     * @param string $name
     *
     * @return ActionDefiner
     */
    public function action($name)
    {
        return new ActionDefiner($this->authSystem, $name, function (IAction $action) {
            $this->actions[$action->getName()] = $action;

            foreach ($action->getRequiredPermissions() as $permission) {
                $this->permissions[] = $permission;
            }
        });
    }

    /**
     * Defines a table data source with the supplied name.
     *
     * @param string $name
     *
     * @return TableDefiner
     */
    public function table($name)
    {
        return new TableDefiner($name, function (ITableDataSource $tableDataSource) {
            $this->tables[$tableDataSource->getName()] = $tableDataSource;
        });
    }

    /**
     * @return FinalizedModuleDefinition
     */
    public function finalize()
    {

    }
}