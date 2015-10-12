<?php

namespace Iddigital\Cms\Core\Table\Chart\Data;

use Iddigital\Cms\Core\Table\Chart\IChartDataTable;
use Iddigital\Cms\Core\Table\Chart\IChartStructure;

/**
 * The chart data table class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartDataTable implements IChartDataTable
{
    /**
     * @var IChartStructure
     */
    protected $structure;

    /**
     * @var array[]
     */
    protected $rows;

    /**
     * ChartDataTable constructor.
     *
     * @param IChartStructure $structure
     * @param array[]           $rows
     */
    public function __construct(IChartStructure $structure, array $rows)
    {
        $this->structure = $structure;
        $this->rows      = $rows;
    }

    /**
     * @return IChartStructure
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * {@inheritdoc}
     */
    public function getRows()
    {
        return $this->rows;
    }
}