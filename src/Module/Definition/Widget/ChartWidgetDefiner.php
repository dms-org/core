<?php declare(strict_types = 1);

namespace Dms\Core\Module\Definition\Widget;

use Dms\Core\Table\Chart\IChartDataSource;
use Dms\Core\Widget\ChartWidget;
use Dms\Core\Widget\TableWidget;

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
    public function __construct(string $name, string $label, IChartDataSource $chart, callable $callback)
    {
        parent::__construct($name, null, null, null, $callback);
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