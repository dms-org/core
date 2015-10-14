<?php

namespace Iddigital\Cms\Core\Module\Definition\Widget;

use Iddigital\Cms\Core\Table\Chart\IChartDataSource;
use Iddigital\Cms\Core\Table\ITableDataSource;

/**
 * The widget definer base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class WidgetDefinerBase
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ITableDataSource[]|null
     */
    protected $tables;

    /**
     * @var IChartDataSource[]|null
     */
    protected $charts;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * WidgetDefiner constructor.
     *
     * @param string                  $name
     * @param ITableDataSource[]|null $tables
     * @param IChartDataSource[]|null $charts
     * @param callable                $callback
     */
    public function __construct($name, array $tables = null, array $charts = null, callable $callback)
    {
        $this->name     = $name;
        $this->tables   = $tables;
        $this->charts   = $charts;
        $this->callback = $callback;
    }
}