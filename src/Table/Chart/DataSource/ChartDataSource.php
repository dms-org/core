<?php declare(strict_types = 1);

namespace Dms\Core\Table\Chart\DataSource;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Table\Chart\Criteria\ChartCriteria;
use Dms\Core\Table\Chart\Data\ChartDataTable;
use Dms\Core\Table\Chart\IChartCriteria;
use Dms\Core\Table\Chart\IChartDataSource;
use Dms\Core\Table\Chart\IChartStructure;

/**
 * The chart data source base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ChartDataSource implements IChartDataSource
{
    /**
     * @var IChartStructure
     */
    protected $structure;

    /**
     * ChartDataSource constructor.
     *
     * @param IChartStructure $structure
     */
    public function __construct(IChartStructure $structure)
    {
        $this->structure = $structure;
    }

    /**
     * @inheritDoc
     */
    final public function getStructure() : \Dms\Core\Table\Chart\IChartStructure
    {
        return $this->structure;
    }

    /**
     * @inheritDoc
     */
    final public function criteria() : \Dms\Core\Table\Chart\Criteria\ChartCriteria
    {
        return new ChartCriteria($this->structure);
    }

    /**
     * @inheritDoc
     */
    final public function load(IChartCriteria $criteria = null) : \Dms\Core\Table\Chart\IChartDataTable
    {
        $this->verifyCriteria(__METHOD__, $criteria);

        return new ChartDataTable($this->structure, $this->loadData($criteria));
    }

    /**
     * @param IChartCriteria|null $criteria
     *
     * @return array[]
     */
    abstract protected function loadData(IChartCriteria $criteria = null) : array;

    protected function verifyCriteria($method, IChartCriteria $criteria = null)
    {
        if ($criteria && $criteria->getStructure() !== $this->structure) {
            throw InvalidArgumentException::format(
                    'Invalid criteria passed to %s: structure does not match structure from this data source',
                    $method
            );
        }
    }
}