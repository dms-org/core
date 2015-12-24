<?php

namespace Dms\Core\Table\DataSource;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Collection;
use Dms\Core\Table\Chart\DataSource\ChartTableDataSourceAdapter;
use Dms\Core\Table\Chart\DataSource\Definition\ChartTableMapperDefinition;
use Dms\Core\Table\Criteria\RowCriteria;
use Dms\Core\Table\Data\DataTable;
use Dms\Core\Table\Data\TableRow;
use Dms\Core\Table\Data\TableSection;
use Dms\Core\Table\IRowCriteria;
use Dms\Core\Table\ITableDataSource;
use Dms\Core\Table\ITableRow;
use Dms\Core\Table\ITableSection;
use Dms\Core\Table\ITableStructure;
use Pinq\ITraversable;

/**
 * The table data source class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class TableDataSource implements ITableDataSource
{
    /**
     * @var ITableStructure
     */
    protected $structure;

    /**
     * TableDataSource constructor.
     *
     * @param ITableStructure $structure
     */
    public function __construct(ITableStructure $structure)
    {
        $this->structure = $structure;
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
        return new RowCriteria($this->structure);
    }

    /**
     * @inheritDoc
     */
    public function asChart(callable $chartMappingCallback)
    {
        $definition = new ChartTableMapperDefinition($this);
        $chartMappingCallback($definition);

        return new ChartTableDataSourceAdapter($definition->finalize());
    }

    /**
     * @return RowCriteria
     */
    protected function defaultLoadCriteria()
    {
        return $this->criteria()->loadAll();
    }

    /**
     * @inheritDoc
     */
    final public function load(IRowCriteria $criteria = null)
    {
        $this->verifyCriteria($criteria);

        $rows     = $this->loadRows($criteria);
        $sections = $this->performRowGrouping($rows, $criteria);

        return new DataTable($this->structure, $sections);
    }

    /**
     * @param IRowCriteria|null $criteria
     *
     * @return ITableRow[]
     */
    abstract protected function loadRows(IRowCriteria $criteria = null);

    /**
     * @inheritDoc
     */
    final public function count(IRowCriteria $criteria = null)
    {
        $this->verifyCriteria($criteria);

        return $this->loadCount($criteria);
    }

    /**
     * @param IRowCriteria|null $criteria
     *
     * @return int
     */
    abstract protected function loadCount(IRowCriteria $criteria = null);

    /**
     * @param ITableRow[]       $rows
     * @param IRowCriteria|null $criteria
     *
     * @return ITableSection[]
     */
    protected function performRowGrouping(array $rows, IRowCriteria $criteria = null)
    {
        $collection = new Collection($rows);

        if ($criteria && $criteria->getGroupings()) {
            $groupings  = $criteria->getGroupings();
            $collection = $collection->groupBy(function (ITableRow $row) use ($groupings) {
                $groupData = [];

                foreach ($groupings as $grouping) {
                    $columnName                             = $grouping->getColumn()->getName();
                    $componentName                          = $grouping->getColumnComponent()->getName();
                    $groupData[$columnName][$componentName] = $row->getData()[$columnName][$componentName];
                }

                return $groupData;
            });
        } else {
            $collection = $collection->groupBy(function () {
                return null;
            });
        }

        return $collection
                ->select(function (ITraversable $section, array $groupData = null) {
                    return new TableSection(
                            $this->structure,
                            $groupData ? new TableRow($groupData) : null,
                            $section->asArray()
                    );
                })
                ->asArray();
    }

    final protected function verifyCriteria(IRowCriteria $criteria = null)
    {
        if ($criteria && $criteria->getStructure() !== $this->structure) {
            throw InvalidArgumentException::format(
                    'Invalid criteria: table structure does not match data source table structure'
            );
        }
    }
}