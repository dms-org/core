<?php

namespace Dms\Core\Module\Definition;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Module\Definition\Chart\ChartDefiner;
use Dms\Core\Module\Definition\Table\TableDefiner;
use Dms\Core\Module\Definition\Widget\WidgetLabelDefiner;
use Dms\Core\Module\IAction;
use Dms\Core\Module\IChartDisplay;
use Dms\Core\Module\ITableDisplay;
use Dms\Core\Widget\IWidget;

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
    protected $authSystem;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var IAction[]
     */
    protected $actions = [];

    /**
     * @var ITableDisplay[]
     */
    protected $tables = [];

    /**
     * @var IChartDisplay[]
     */
    protected $charts = [];

    /**
     * @var IWidget[]
     */
    protected $widgets = [];

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
     * @return IAuthSystem
     */
    public function getAuthSystem()
    {
        return $this->authSystem;
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
        return new TableDefiner($name, function (ITableDisplay $table) {
            $this->tables[$table->getName()] = $table;
        });
    }

    /**
     * Defines a chart data source with the supplied name.
     *
     * @param string $name
     *
     * @return ChartDefiner
     */
    public function chart($name)
    {
        return new ChartDefiner($name, $this->tables, function (IChartDisplay $chart) {
            $this->charts[$chart->getName()] = $chart;
        });
    }

    /**
     * Defines a widget with the supplied name.
     *
     * @param string $name
     *
     * @return WidgetLabelDefiner
     */
    public function widget($name)
    {
        return new WidgetLabelDefiner($name, $this->tables, $this->charts, function (IWidget $widget) {
            $this->widgets[$widget->getName()] = $widget;
        });
    }

    /**
     * Gets the fluent custom properties definer.
     *
     * @return CustomPropertiesDefiner
     */
    public function custom()
    {
        return new CustomPropertiesDefiner($this->actions, $this->tables, $this->charts);
    }

    /**
     * @return FinalizedModuleDefinition
     * @throws InvalidOperationException
     */
    public function finalize()
    {
        $this->verifyCanBeFinalized();

        return new FinalizedModuleDefinition(
                $this->name,
                $this->actions,
                $this->tables,
                $this->charts,
                $this->widgets
        );
    }

    /**
     * @return void
     * @throws InvalidOperationException
     */
    protected function verifyCanBeFinalized()
    {
        if (!$this->name) {
            throw InvalidOperationException::format('Cannot finalize module definition: name has not been defined');
        }
    }
}