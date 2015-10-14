<?php

namespace Iddigital\Cms\Core\Module\Definition\Widget;

use Iddigital\Cms\Core\Table\Chart\IChartDataSource;
use Iddigital\Cms\Core\Widget\ChartWidget;
use Iddigital\Cms\Core\Widget\TableWidget;

/**
 * The chart widget definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartWidgetDefiner extends WidgetDefinerBase
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var IChartDataSource
     */
    private $chart;

    /**
     * TableWidgetDefiner constructor.
     *
     * @param string           $name
     * @param string           $label
     * @param IChartDataSource $chart
     * @param callable         $callback
     */
    public function __construct($name, $label, IChartDataSource $chart, callable $callback)
    {
        parent::__construct($name, null, null, $callback);
        $this->label = $label;
        $this->chart = $chart;
    }

    /**
     * Defines the chart criteria for the widget.
     *
     * Example:
     * <code>
     * ->matching(function (ChartCriteria $criteria) {
     *      $criteria->where('column', '>', 500);
     * });
     * </code>
     *
     * @see ChartCriteria
     *
     * @param callable $criteriaDefinitionCallback
     *
     * @return void
     */
    public function matching(callable $criteriaDefinitionCallback)
    {
        $criteria = $this->chart->criteria();
        $criteriaDefinitionCallback($criteria);

        call_user_func($this->callback, new ChartWidget($this->name, $this->label, $this->chart, $criteria));
    }

    /**
     * Defines the table to load all the chart data (empty criteria).
     *
     * @return void
     */
    public function allData()
    {
        call_user_func($this->callback, new ChartWidget($this->name, $this->label, $this->chart));
    }
}