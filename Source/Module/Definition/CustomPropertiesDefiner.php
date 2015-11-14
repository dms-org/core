<?php

namespace Iddigital\Cms\Core\Module\Definition;

use Iddigital\Cms\Core\Module\IAction;
use Iddigital\Cms\Core\Module\IChartDisplay;
use Iddigital\Cms\Core\Module\ITableDisplay;

/**
 * The custom module properties definer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomPropertiesDefiner
{
    /**
     * @var IAction[]
     */
    protected $actions;

    /**
     * @var ITableDisplay[]
     */
    protected $tables;

    /**
     * @var IChartDisplay[]
     */
    protected $charts;

    /**
     * CustomPropertiesDefiner constructor.
     *
     * @param IAction[] $actions
     * @param ITableDisplay[] $tables
     * @param IChartDisplay[] $charts
     */
    public function __construct(array &$actions, array &$tables, array &$charts)
    {
        $this->actions = $actions;
        $this->tables  = $tables;
        $this->charts  = $charts;
    }


    /**
     * Appends a custom action
     *
     * @param IAction $action
     *
     * @return void
     */
    public function action(IAction $action)
    {
        $this->actions[$action->getName()] = $action;
    }

    /**
     * Appends a custom table
     *
     * @param ITableDisplay $table)
     *
     * @return void
     */
    public function table(ITableDisplay $table)
    {
        $this->tables[$table->getName()] = $table;
    }

    /**
     * Appends a custom chart
     *
     * @param IChartDisplay $chart)
     *
     * @return void
     */
    public function chart(IChartDisplay $chart)
    {
        $this->charts[$chart->getName()] = $chart;
    }
}