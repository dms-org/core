<?php

namespace Iddigital\Cms\Core\Module\Definition;

use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Module\IAction;
use Iddigital\Cms\Core\Table\Chart\IChartDataSource;
use Iddigital\Cms\Core\Table\ITableDataSource;
use Iddigital\Cms\Core\Widget\IWidget;

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
     * @var IChartDataSource[]
     */
    private $charts;

    /**
     * @var IWidget[]
     */
    private $widgets;

    /**
     * FinalizedModuleDefinition constructor.
     *
     * @param string             $name
     * @param IAction[]          $actions
     * @param ITableDataSource[] $tables
     */
    public function __construct($name, array $actions, array $tables, array $charts, array $widgets)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'actions', $actions, IAction::class);
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'tables', $tables, ITableDataSource::class);
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'charts', $charts, IChartDataSource::class);
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'widgets', $widgets, IWidget::class);

        $this->name    = $name;
        $this->actions = $actions;
        $this->tables  = $tables;
        $this->charts  = $charts;
        $this->widgets = $widgets;

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

    /**
     * @return IChartDataSource[]
     */
    public function getCharts()
    {
        return $this->charts;
    }

    /**
     * @return IWidget[]
     */
    public function getWidgets()
    {
        return $this->widgets;
    }
}