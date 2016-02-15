<?php declare(strict_types = 1);

namespace Dms\Core\Package\Definition;

/**
 * The dashboard widget definer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DashboardWidgetDefiner
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * DashboardWidgetDefiner constructor.
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Defines the widget names in the dashboard.
     *
     * Example:
     * <code>
     * ->widgets([
     *      'some-module-name.some-widget-name',
     *      'products.recent-table',
     * ]);
     * </code>
     *
     * @param string[] $widgetNames
     *
     * @return void
     */
    public function widgets(array $widgetNames)
    {
        call_user_func($this->callback, $widgetNames);
    }
}