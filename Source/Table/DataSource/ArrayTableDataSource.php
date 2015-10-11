<?php

namespace Iddigital\Cms\Core\Table\DataSource;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\Traversable;
use Iddigital\Cms\Core\Table\Criteria\ColumnOrdering;
use Iddigital\Cms\Core\Table\Data\TableRow;
use Iddigital\Cms\Core\Table\IRowCriteria;
use Iddigital\Cms\Core\Table\ITableRow;
use Iddigital\Cms\Core\Table\ITableSection;
use Iddigital\Cms\Core\Table\ITableStructure;
use Iddigital\Cms\Core\Util\Debug;
use Pinq\Direction;

/**
 * The array table data source.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayTableDataSource extends TableDataSource
{
    /**
     * @var ITableRow[]
     */
    protected $rows;

    /**
     * ArrayTableDataSource constructor.
     *
     * @param string $name
     * @param ITableStructure $structure
     * @param array[]         $rows
     *
     * @throws InvalidArgumentException
     */
    public function __construct($name, ITableStructure $structure, array $rows)
    {
        parent::__construct($name, $structure);
        $this->rows = $this->buildNormalizedRows($structure, $rows);
    }

    /**
     * @param ITableStructure $structure
     * @param array           $array
     *
     * @return ITableRow[]
     * @throws InvalidArgumentException
     */
    private function buildNormalizedRows(ITableStructure $structure, array $array)
    {
        $columns           = $structure->getColumns();
        $columnNames       = $structure->getColumnNames();
        $componentNamesMap = [];

        foreach ($columns as $column) {
            $componentNames = $column->getComponentNames();
            sort($componentNames);
            $componentNamesMap[$column->getName()] = $componentNames;
        }

        sort($columnNames);
        $rows = [];

        foreach ($array as $rowData) {
            $rowColumns = array_keys($rowData);
            sort($rowColumns);

            if ($columnNames !== $rowColumns) {
                throw InvalidArgumentException::format(
                        'Invalid row array: expecting columns names to be (%s), (%s) given',
                        Debug::formatValues($columnNames), Debug::formatValues($rowColumns)
                );
            }

            foreach ($rowData as $columnName => $componentData) {
                $column = $columns[$columnName];

                if (!is_array($componentData)) {
                    if ($column->hasSingleComponent()) {
                        $component            = $column->getComponent();
                        $rowData[$columnName] = [$component->getName() => $componentData];
                    } else {
                        throw InvalidArgumentException::format(
                                'Invalid row array: expecting columns data for column %s to be array, %s given',
                                $columnName, Debug::getType($componentData)
                        );
                    }
                } else {
                    $componentNames = array_keys($componentData);
                    sort($componentNames);

                    if ($componentNames !== $componentNamesMap[$columnName]) {
                        throw InvalidArgumentException::format(
                                'Invalid row array: expecting columns data for column %s to have be array with keys (%s), (%s) given',
                                $columnName, Debug::formatValues($componentNamesMap[$columnName]), Debug::formatValues($componentNames)
                        );
                    }
                }
            }

            $rows[] = new TableRow($rowData);
        }

        return $rows;
    }

    /**
     * @param IRowCriteria|null $criteria
     *
     * @return ITableSection[]
     */
    protected function loadRows(IRowCriteria $criteria = null)
    {
        $criteria = $criteria ?: $this->defaultLoadCriteria();

        $collection = Traversable::from($this->rows);

        foreach ($criteria->getConditions() as $condition) {
            $collection = $collection->where($condition->makeRowFilterCallable());
        }

        $orderings = $criteria->getOrderings();
        if (!empty($orderings)) {
            /** @var ColumnOrdering $firstOrdering */
            $firstOrdering = array_shift($orderings);
            $collection    = $collection->orderBy(
                    $firstOrdering->makeComponentGetterCallable(),
                    $firstOrdering->isAsc() ? Direction::ASCENDING : Direction::DESCENDING
            );

            foreach ($orderings as $ordering) {
                $collection = $collection->thenBy(
                        $ordering->makeComponentGetterCallable(),
                        $ordering->isAsc() ? Direction::ASCENDING : Direction::DESCENDING
                );
            }
        }

        $collection = $collection->slice($criteria->getRowsToSkip(), $criteria->getAmountOfRows());

        if (!$criteria->getWhetherLoadsAllColumns()) {
            $columnsToLoadMap = $criteria->getColumnsToLoad();

            $collection = $collection->select(function (TableRow $row) use ($columnsToLoadMap) {
                return new TableRow(array_intersect_key($row->getData(), $columnsToLoadMap));
            });
        }

        return $collection->asArray();
    }

    /**
     * @param IRowCriteria|null $criteria
     *
     * @return int
     */
    protected function loadCount(IRowCriteria $criteria = null)
    {
        return count($this->loadRows($criteria));
    }
}