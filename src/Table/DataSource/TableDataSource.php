<?php declare(strict_types = 1);

namespace Dms\Core\Table\DataSource;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Collection;
use Dms\Core\Table\Chart\DataSource\ChartTableDataSourceAdapter;
use Dms\Core\Table\Chart\DataSource\Definition\ChartTableMapperDefinition;
use Dms\Core\Table\Chart\IChartDataSource;
use Dms\Core\Table\Criteria\RowCriteria;
use Dms\Core\Table\Data\DataTable;
use Dms\Core\Table\Data\TableRow;
use Dms\Core\Table\Data\TableSection;
use Dms\Core\Table\IDataTable;
use Dms\Core\Table\IRowCriteria;
use Dms\Core\Table\ITableDataSource;
use Dms\Core\Table\ITableRow;
use Dms\Core\Table\ITableSection;
use Dms\Core\Table\ITableStructure;
use Dms\Core\Util\Hashing\ValueHasher;

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
    final public function getStructure() : ITableStructure
    {
        return $this->structure;
    }

    /**
     * @inheritDoc
     */
    final public function criteria() : RowCriteria
    {
        return new RowCriteria($this->structure);
    }

    /**
     * @inheritDoc
     */
    public function asChart(callable $chartMappingCallback) : IChartDataSource
    {
        $definition = new ChartTableMapperDefinition($this);
        $chartMappingCallback($definition);

        return new ChartTableDataSourceAdapter($definition->finalize());
    }

    /**
     * @return RowCriteria
     */
    protected function defaultLoadCriteria() : RowCriteria
    {
        return $this->criteria()->loadAll();
    }

    /**
     * @inheritDoc
     */
    final public function load(IRowCriteria $criteria = null) : IDataTable
    {
        $this->verifyCriteria($criteria);

        $structure = $criteria ? $this->structure->withColumns($criteria->getColumnsToLoad()) : $this->structure;
        $rows      = $this->loadRows($criteria);
        $sections  = $this->performRowGrouping($structure, $rows, $criteria);

        return new DataTable($structure, $sections);
    }

    /**
     * @param IRowCriteria|null $criteria
     *
     * @return ITableRow[]
     */
    abstract protected function loadRows(IRowCriteria $criteria = null) : array;

    /**
     * @inheritDoc
     */
    final public function count(IRowCriteria $criteria = null) : int
    {
        $this->verifyCriteria($criteria);

        return $this->loadCount($criteria);
    }

    /**
     * @param IRowCriteria|null $criteria
     *
     * @return int
     */
    abstract protected function loadCount(IRowCriteria $criteria = null) : int;

    /**
     * @param ITableStructure   $structure
     * @param ITableRow[]       $rows
     * @param IRowCriteria|null $criteria
     *
     * @return ITableSection[]
     */
    protected function performRowGrouping(ITableStructure $structure, array $rows, IRowCriteria $criteria = null) : array
    {
        if (empty($rows)) {
            return [];
        }

        $groupDataValuesMap = [];
        $groupedRows        = [];

        if ($criteria && $criteria->getGroupings()) {
            $groupings = $criteria->getGroupings();

            foreach ($rows as $row) {
                $groupData     = [];
                $groupDataHash = '';

                foreach ($groupings as $grouping) {
                    $columnName                             = $grouping->getColumn()->getName();
                    $componentName                          = $grouping->getColumnComponent()->getName();
                    $value                                  = $row->getData()[$columnName][$componentName];
                    $groupData[$columnName][$componentName] = $value;
                    $groupDataHash .= ValueHasher::hash($value) . '||';
                }

                $groupedRows[$groupDataHash][] = $row;

                if (!isset($groupDataValuesMap[$groupDataHash])) {
                    $groupDataValuesMap[$groupDataHash] = $groupData;
                }
            }
        } else {
            $groupedRows[] = $rows;
        }

        $sections = [];

        foreach ($groupedRows as $groupingValueHash => $rows) {
            $sections[] = new TableSection(
                $structure,
                isset($groupDataValuesMap[$groupingValueHash]) ? new TableRow($groupDataValuesMap[$groupingValueHash]) : null,
                $rows
            );
        }


        return $sections;
    }

    final protected function verifyCriteria(IRowCriteria $criteria = null)
    {
        if (!$criteria) {
            return;
        }

        if ($criteria->getStructure() !== $this->structure) {
            throw InvalidArgumentException::format(
                'Invalid criteria: table structure does not match data source table structure'
            );
        }

        if (empty($criteria->getColumnsToLoad())) {
            throw InvalidArgumentException::format(
                'Invalid criteria: no columns have been specified to load'
            );
        }
    }
}