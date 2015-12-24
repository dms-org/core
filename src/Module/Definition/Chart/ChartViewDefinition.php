<?php

namespace Dms\Core\Module\Definition\Chart;

use Dms\Core\Module\IChartView;
use Dms\Core\Module\ITableView;
use Dms\Core\Table\Chart\IChartDataSource;
use Dms\Core\Table\ITableDataSource;

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