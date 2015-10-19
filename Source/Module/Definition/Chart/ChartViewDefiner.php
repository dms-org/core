<?php

namespace Iddigital\Cms\Core\Module\Definition\Chart;

use Iddigital\Cms\Core\Module\Chart\ChartView;
use Iddigital\Cms\Core\Module\Table\TableView;
use Iddigital\Cms\Core\Table\Chart\Criteria\ChartCriteria;
use Iddigital\Cms\Core\Table\Chart\IChartDataSource;

/**
 * The chart view definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartViewDefiner extends ChartCriteria
{
    /**
     * @var IChartDataSource
     */
    private $dataSource;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $label;

    /**
     * @var bool
     */
    private $default = false;

    /**
     * ChartViewDefiner constructor.
     *
     * @param IChartDataSource $dataSource
     * @param string           $name
     * @param string           $label
     */
    public function __construct(IChartDataSource $dataSource, $name, $label)
    {
        parent::__construct($dataSource->getStructure());

        $this->dataSource = $dataSource;
        $this->name       = $name;
        $this->label      = $label;
    }

    /**
     * Defines this view to be the default view.
     *
     * @return static
     */
    public function asDefault()
    {
        $this->default = true;

        return $this;
    }

    /**
     * @return ChartView
     */
    public function finalize()
    {
        return new ChartView(
                $this->name,
                $this->label,
                $this->default,
                ChartCriteria::fromExisting($this)
        );
    }
}