<?php

namespace Iddigital\Cms\Core\Table\DataSource;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\Collection;
use Iddigital\Cms\Core\Table\Criteria\RowCriteria;
use Iddigital\Cms\Core\Table\Data\DataTable;
use Iddigital\Cms\Core\Table\Data\TableRow;
use Iddigital\Cms\Core\Table\Data\TableSection;
use Iddigital\Cms\Core\Table\IRowCriteria;
use Iddigital\Cms\Core\Table\ITableDataSource;
use Iddigital\Cms\Core\Table\ITableRow;
use Iddigital\Cms\Core\Table\ITableSection;
use Iddigital\Cms\Core\Table\ITableStructure;
use Pinq\ITraversable;

/**
 * The table data source class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class TableDataSource implements ITableDataSource
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ITableStructure
     */
    protected $structure;

    /**
     * TableDataSource constructor.
     *
     * @param string          $name
     * @param ITableStructure $structure
     */
    public function __construct($name, ITableStructure $structure)
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
        return new RowCriteria($this->structure);
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