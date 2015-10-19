<?php

namespace Iddigital\Cms\Core\Module\Definition\Chart;

use Iddigital\Cms\Core\Module\IChartView;
use Iddigital\Cms\Core\Module\ITableView;
use Iddigital\Cms\Core\Table\Chart\IChartDataSource;
use Iddigital\Cms\Core\Table\ITableDataSource;

/**
 * The table views definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartViewDefinition
{
    /**
     * @var ITableDataSource
     */
    private $dataSource;

    /**
     * @var ChartViewDefiner[]
     */
    private $viewDefiners = [];

    /**
     * ChartViewDefinition constructor.
     *
     * @param IChartDataSource $dataSource
     */
    public function __construct(IChartDataSource $dataSource)
    {
        $this->dataSource = $dataSource;
    }

    /**
     * Defines a new view with the supplied name and label.
     *
     * @param string $name
     * @param string $label
     *
     * @return ChartViewDefiner
     */
    public function name($name, $label)
    {
        $definer = new ChartViewDefiner($this->dataSource, $name, $label);

        $this->viewDefiners[] = $definer;

        return $definer;
    }

    /**
     * @return IChartView[]
     */
    public function finalize()
    {
        $views = [];

        foreach ($this->viewDefiners as $definer) {
            $views[] = $definer->finalize();
        }

        return $views;
    }
}