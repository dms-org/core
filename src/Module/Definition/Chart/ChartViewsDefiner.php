<?php declare(strict_types = 1);

namespace Dms\Core\Module\Definition\Chart;

use Dms\Core\Module\Chart\ChartDisplay;
use Dms\Core\Table\Chart\IChartDataSource;

/**
 * The table views definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartViewsDefiner
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var IChartDataSource
     */
    protected $dataSource;

    /**
     * ChartViewsDefiner constructor.
     *
     * @param string           $name
     * @param callable         $callback
     * @param IChartDataSource $dataSource
     */
    public function __construct(string $name, callable $callback, IChartDataSource $dataSource)
    {
        $this->name       = $name;
        $this->callback   = $callback;
        $this->dataSource = $dataSource;
    }

    /**
     * Defines the views of the chart
     *
     * Example:
     * <code>
     * ->withViews(function (ChartViewDefinition $view) {
     *      $view->name('default', 'Default')
     *          ->asDefault()
     *          ->orderByAsc('axis-name');
     * });
     * </code>
     *
     * @param callable $viewsDefinitionCallback
     *
     * @return void
     */
    public function withViews(callable $viewsDefinitionCallback)
    {
        $definition = new ChartViewDefinition($this->dataSource);
        $viewsDefinitionCallback($definition);
        $views = $definition->finalize();

        call_user_func($this->callback, new ChartDisplay($this->name, $this->dataSource, $views));
    }

    /**
     * Defines the chart display to have no predefined views.
     *
     * @return void
     */
    public function withoutViews()
    {
        $this->withViews(function () {

        });
    }
}