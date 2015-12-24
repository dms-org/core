<?php

namespace Dms\Core\Table\Chart\DataSource\Definition;

use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Table\Chart\IChartStructure;
use Dms\Core\Table\IColumn;
use Dms\Core\Table\IColumnComponent;
use Dms\Core\Table\ITableDataSource;

/**
 * The chart table mapping definition.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartTableMapperDefinition
{
    /**
     * @var ITableDataSource
     */
    protected $tableDataSource;

    /**
     * @var string[]
     */
    protected $tableColumnsToLoad = [];

    /**
     * @var IChartStructure
     */
    protected $structure;

    /**
     * @var \SplObjectStorage
     */
    protected $chartComponentTableIdMap;

    /**
     * @var \SplObjectStorage
     */
    protected $chartComponentCallableMap;

    /**
     * ChartTableMapperDefinition constructor.
     *
     * @param ITableDataSource $tableDataSource
     */
    public function __construct(ITableDataSource $tableDataSource)
    {
        $this->tableDataSource           = $tableDataSource;
        $this->chartComponentTableIdMap  = new \SplObjectStorage();
        $this->chartComponentCallableMap = new \SplObjectStorage();
    }

    /**
     * Defines the structure of the chart.
     *
     * @param IChartStructure $structure
     *
     * @return void
     */
    public function structure(IChartStructure $structure)
    {
        $this->structure = $structure;
    }

    /**
     * Defines a table component to map to a chart component.
     *
     * @param string $componentId
     *
     * @return TableComponentMappingDefiner
     */
    public function column($componentId)
    {
        /** @var IColumn $column */
        /** @var IColumnComponent $component */
        list($column, $component) = $this->tableDataSource->getStructure()->getColumnAndComponent($componentId);
        $componentId = $column->getName() . '.' . $component->getName();

        return new TableComponentMappingDefiner($component, function (IColumnComponent $chartAxisComponent) use ($column, $componentId) {
            $this->chartComponentTableIdMap[$chartAxisComponent] = $componentId;
            $this->addColumnToLoad($column);
        });
    }

    /**
     * Defines a table
     *
     * @param callable $callback
     *
     * @return ComputedComponentDefiner
     */
    public function computed(callable $callback)
    {
        return new ComputedComponentDefiner(function (IColumnComponent $chartAxisComponent, array $columnsToLoad) use ($callback) {
            $this->chartComponentCallableMap[$chartAxisComponent] = $callback;

            foreach ($columnsToLoad as $columnToLoad) {
                $column = $this->tableDataSource->getStructure()->getColumn($columnToLoad);
                $this->addColumnToLoad($column);
            }
        });
    }

    protected function addColumnToLoad(IColumn $column)
    {
        $this->tableColumnsToLoad[$column->getName()] = $column->getName();
    }

    /**
     * @return FinalizedChartTableMapperDefinition
     * @throws InvalidOperationException
     */
    public function finalize()
    {
        if (!$this->structure) {
            throw InvalidOperationException::format('Chart structure must be set: use $map->structure(...)');
        }

        $tableChartComponentIdMap    = [];
        $chartComponentIdCallableMap = [];

        foreach ($this->structure->getAxes() as $axis) {
            $axisName = $axis->getName();

            foreach ($axis->getComponents() as $component) {
                $chartComponentId = $axisName . '.' . $component->getName();

                if ($this->chartComponentTableIdMap->contains($component)) {
                    $tableComponentId                            = $this->chartComponentTableIdMap[$component];
                    $tableChartComponentIdMap[$tableComponentId] = $chartComponentId;
                } elseif ($this->chartComponentCallableMap->contains($component)) {
                    $callable                                       = $this->chartComponentCallableMap[$component];
                    $chartComponentIdCallableMap[$chartComponentId] = $callable;
                } else {
                    throw InvalidOperationException::format(
                            'Could not find chart component \'%s\' within defined mapped chart components, ensure that all components are registered via the definition',
                            $chartComponentId
                    );
                }
            }
        }

        return new FinalizedChartTableMapperDefinition(
                $this->tableDataSource,
                array_values($this->tableColumnsToLoad),
                $this->structure,
                $tableChartComponentIdMap,
                $chartComponentIdCallableMap
        );
    }
}