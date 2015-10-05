<?php

namespace Iddigital\Cms\Core\Module\Definition;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Module\Definition\Action\ActionDefiner;
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
            $this->actions[] = $action;

            foreach ($action->getRequiredPermissions() as $permission) {
                $this->permissions[] = $permission;
            }
        });
    }
}