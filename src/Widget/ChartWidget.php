<?php

namespace Dms\Core\Widget;

use Dms\Core\Table\Chart\IChartCriteria;
use Dms\Core\Table\Chart\IChartDataSource;
use Dms\Core\Table\Chart\IChartDataTable;

/**
 * The chart widget class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartWidget extends Widget
{
    /**
     * @var IChartDataSource
     */
    protected $chartDataSource;

    /**
     * @var IChartCriteria|null
     */
    protected $criteria;

    /**
     * @inheritDoc
     */
    public function __construct($name, $label, IChartDataSource $chartDataSource, IChartCriteria $criteria = null)
    {
        parent::__construct($name, $label);
        $this->chartDataSource = $chartDataSource;
        $this->criteria        = $criteria;
    }

    /**
     * @return IChartDataSource
     */
    public function getChartDataSource()
    {
        return $this->chartDataSource;
    }

    /**
     * @return IChartCriteria|null
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @return bool
     */
    public function hasCriteria()
    {
        return $this->criteria !== null;
    }

    /**
     * @return IChartDataTable
     */
    public function loadData()
    {
        return $this->chartDataSource->load($this->criteria);
    }

    /**
     * Returns whether the current user authorized to see this widget.
     *
     * @return bool
     */
    public function isAuthorized()
    {
        return true;
    }
}