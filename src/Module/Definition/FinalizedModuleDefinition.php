<?php declare(strict_types = 1);

namespace Dms\Core\Module\Definition;

use Dms\Core\Auth\IPermission;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\IAction;
use Dms\Core\Module\IChartDisplay;
use Dms\Core\Module\ITableDisplay;
use Dms\Core\Table\Chart\IChartDataSource;
use Dms\Core\Table\ITableDataSource;
use Dms\Core\Widget\IWidget;

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
     * @param string          $name
     * @param IAction[]       $actions
     * @param ITableDisplay[] $tables
     * @param IChartDisplay[] $charts
     * @param IWidget[]       $widgets
     */
    public function __construct(string $name, array $actions, array $tables, array $charts, array $widgets)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'actions', $actions, IAction::class);
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'tables', $tables, ITableDisplay::class);
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'charts', $charts, IChartDisplay::class);
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
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return IPermission[]
     */
    public function getPermissions() : array
    {
        return $this->permissions;
    }

    /**
     * @return IAction[]
     */
    public function getActions() : array
    {
        return $this->actions;
    }

    /**
     * @return ITableDisplay[]
     */
    public function getTables() : array
    {
        return $this->tables;
    }

    /**
     * @return IChartDisplay[]
     */
    public function getCharts() : array
    {
        return $this->charts;
    }

    /**
     * @return IWidget[]
     */
    public function getWidgets() : array
    {
        return $this->widgets;
    }
}