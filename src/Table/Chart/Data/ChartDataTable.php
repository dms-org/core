<?php declare(strict_types = 1);

namespace Dms\Core\Table\Chart\Data;

use Dms\Core\Table\Chart\IChartDataTable;
use Dms\Core\Table\Chart\IChartStructure;

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
    public function getStructure() : \Dms\Core\Table\Chart\IChartStructure
    {
        return $this->structure;
    }

    /**
     * {@inheritdoc}
     */
    public function getRows() : array
    {
        return $this->rows;
    }
}