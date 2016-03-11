<?php declare(strict_types = 1);

namespace Dms\Core\Table\DataSource\Definition;

use Dms\Core\Table\IColumn;
use Dms\Core\Table\ITableDataSource;
use Dms\Core\Table\ITableStructure;
use Dms\Core\Table\TableStructure;

/**
 * The grouped table data source definition.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class GroupedTableDefinition
{
    /**
     * @var ITableDataSource
     */
    protected $dataSource;

    /**
     * @var ITableStructure
     */
    protected $structure;

    /**
     * @var string[]
     */
    protected $groupByComponentIds;

    /**
     * @var IColumn[]
     */
    protected $columns;

    /**
     * @var callable[]
     */
    protected $componentIdCallableMap;

    /**
     * GroupedTableDataSourceDefinition constructor.
     *
     * @param ITableDataSource $dataSource
     */
    public function __construct(ITableDataSource $dataSource)
    {
        $this->dataSource = $dataSource;
        $this->structure  = $dataSource->getStructure();
    }

    /**
     * Adds a column to the table.
     *
     * @param IColumn $column
     *
     * @return void
     */
    public function column(IColumn $column)
    {
        $this->columns[$column->getName()] = $column;
    }

    /**
     * @param string $componentId
     *
     * @return void
     */
    public function groupedBy(string $componentId)
    {
        $this->groupByComponentIds[] = $this->structure->normalizeComponentId($componentId);
    }

    /**
     * Defines a callback to get the data from each group for the mapped column component.
     *
     * @param callable $groupDataCallback
     *
     * @return ColumnMappingDefiner
     */
    public function computed(callable $groupDataCallback) : ColumnMappingDefiner
    {
        return new ColumnMappingDefiner(
            function (IColumn $column) use ($groupDataCallback) {
                $this->column($column);
                $this->componentIdCallableMap[$column->getComponentId()] = $groupDataCallback;
            },
            function ($componentId) use ($groupDataCallback) {
                $this->componentIdCallableMap[$componentId] = $groupDataCallback;
            }
        );
    }

    /**
     * @param string $componentId
     *
     * @return ColumnMappingDefiner
     */
    public function sum(string $componentId) : ColumnMappingDefiner
    {
        return $this->aggregate($componentId, 'array_sum');
    }

    /**
     * @param string $componentId
     *
     * @return ColumnMappingDefiner
     */
    public function average(string $componentId) : ColumnMappingDefiner
    {
        return $this->aggregate($componentId, function (array $values) {
            return (float)(array_sum($values) / count($values));
        });
    }

    /**
     * @return ColumnMappingDefiner
     */
    public function count() : ColumnMappingDefiner
    {
        return $this->computed(function (array $rows) {
            return count($rows);
        });
    }

    /**
     * @param string   $componentId
     * @param callable $aggregateCallback
     *
     * @return ColumnMappingDefiner
     */
    public function aggregate(string $componentId, callable $aggregateCallback) : ColumnMappingDefiner
    {
        $componentId = $this->structure->normalizeComponentId($componentId);
        list($column, $component) = explode('.', $componentId);

        return $this->computed(function (array $rows) use ($column, $component, $aggregateCallback) {
            return $aggregateCallback(array_column(array_column($rows, $column), $component));
        });
    }

    public function finalize() : FinalizedGroupedTableDefinition
    {
        $groupByColumnComponents = [];

        foreach ($this->groupByComponentIds as $componentId) {
            list($column, $component) = $this->structure->getColumnAndComponent($componentId);
            $groupByColumnComponents[$column->getName()][] = $component;
        }

        $groupByColumns = [];
        foreach ($groupByColumnComponents as $columnName => $components) {
            $groupByColumns[] = $this->structure->getColumn($columnName)->withComponents($components);
        }

        return new FinalizedGroupedTableDefinition(
            $this->dataSource,
            new TableStructure(array_merge($groupByColumns, $this->columns)),
            $this->groupByComponentIds,
            $this->componentIdCallableMap
        );
    }
}