<?php

namespace Iddigital\Cms\Core\Table\Chart\DataSource;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Table\Chart\Criteria\ChartCriteria;
use Iddigital\Cms\Core\Table\Chart\Data\ChartDataTable;
use Iddigital\Cms\Core\Table\Chart\IChartCriteria;
use Iddigital\Cms\Core\Table\Chart\IChartDataSource;
use Iddigital\Cms\Core\Table\Chart\IChartStructure;

/**
 * The chart data source base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ChartDataSource implements IChartDataSource
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var IChartStructure
     */
    protected $structure;

    /**
     * ChartDataSource constructor.
     *
     * @param string          $name
     * @param IChartStructure $structure
     */
    public function __construct($name, IChartStructure $structure)
    {
        $this->name      = $name;
        $this->structure = $structure;
    }

    /**
     * @inheritDoc
     */
    final public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    final public function getStructure()
    {
        return $this->structure;
    }

    /**
     * @inheritDoc
     */
    final public function criteria()
    {
        return new ChartCriteria($this->structure);
    }

    /**
     * @inheritDoc
     */
    final public function load(IChartCriteria $criteria = null)
    {
        $this->verifyCriteria(__METHOD__, $criteria);

        return new ChartDataTable($this->structure, $this->loadData($criteria));
    }

    /**
     * @param IChartCriteria|null $criteria
     *
     * @return array[]
     */
    abstract protected function loadData(IChartCriteria $criteria = null);

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