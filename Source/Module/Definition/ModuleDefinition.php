<?php

namespace Iddigital\Cms\Core\Module\Definition;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Exception\InvalidOperationException;
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
     * @var string
     */
    private $name;

    /**
     * @var IAction[]
     */
    private $actions = [];

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
     * Defines the name of the module.
     *
     * @param string $name
     *
     * @return void
     */
    public function name($name)
    {
        $this->name = $name;
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
     * @throws InvalidOperationException
     */
    public function finalize()
    {
        if (!$this->name) {
            throw InvalidOperationException::format('Cannot finalize module definition: name has not been defined');
        }

        return new FinalizedModuleDefinition(
                $this->name,
                $this->actions,
                $this->tables
        );
    }
}