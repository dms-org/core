<?php

namespace Dms\Core\Table\Chart\DataSource;

use Dms\Core\Table\Chart\DataSource\Criteria\ChartTableCriteriaMapper;
use Dms\Core\Table\Chart\DataSource\Definition\FinalizedChartTableMapperDefinition;
use Dms\Core\Table\Chart\IChartCriteria;
use Dms\Core\Table\ITableDataSource;

/**
 * The table data source adapter class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartTableDataSourceAdapter extends ChartDataSource
{
    /**
     * @var FinalizedChartTableMapperDefinition
     */
    protected $definition;

    /**
     * @var ITableDataSource
     */
    protected $tableDataSource;

    /**
     * @var ChartTableCriteriaMapper
     */
    protected $criteriaMapper;

    /**
     * @var array[]
     */
    protected $tableChartComponentIdMap = [];

    /**
     * @var array[]
     */
    protected $componentIdCallableMap = [];

    /**
     * @param FinalizedChartTableMapperDefinition $definition
     */
    public function __construct(FinalizedChartTableMapperDefinition $definition)
    {
        parent::__construct($definition->getStructure());
        $this->definition             = $definition;
        $this->tableDataSource        = $definition->getTableDataSource();
        $this->criteriaMapper         = new ChartTableCriteriaMapper($definition);

        foreach ($this->definition->getTableToChartComponentIdMap() as $tableComponentId => $chartComponentId) {
            list($tableColumn, $columnComponent) = explode('.', $tableComponentId);
            list($chartAxis, $axisComponent) = explode('.', $chartComponentId);

            $this->tableChartComponentIdMap[] = [
                    $tableColumn,
                    $columnComponent,
                    $chartAxis,
                    $axisComponent,
            ];
        }

        foreach ($this->definition->getComponentIdCallableMap() as $chartComponentId => $callable) {
            list($chartAxis, $axisComponent) = explode('.', $chartComponentId);

            $this->componentIdCallableMap[] = [
                    $chartAxis,
                    $axisComponent,
                    $callable
            ];
        }
    }

    /**
     * @return FinalizedChartTableMapperDefinition
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @param IChartCriteria|null $criteria
     *
     * @return array[]
     */
    protected function loadData(IChartCriteria $criteria = null)
    {
        $criteria = $criteria ?: $this->criteria();

        $sections = $this->tableDataSource->load($this->criteriaMapper->mapCriteria($criteria))->getSections();

        if (empty($sections)) {
            return [];
        }

        // There will be no groupings so only one section
        /** @see ChartTableCriteriaMapper::mapCriteria */
        $tableRows = $sections[0]->getRows();
        $chartRows = [];

        foreach ($this->tableChartComponentIdMap as list($tableColumn, $columnComponent, $chartAxis, $axisComponent)) {
            /** @var string $tableColumn */
            /** @var string $columnComponent */
            /** @var string $chartAxis */
            /** @var string $axisComponent */
            foreach ($tableRows as $key => $tableRow) {
                $chartRows[$key][$chartAxis][$axisComponent] = $tableRow->getCellComponentData($tableColumn, $columnComponent);
            }
        }


        foreach ($this->componentIdCallableMap as list($chartAxis, $axisComponent, $callable)) {
            /** @var string $chartAxis */
            /** @var string $axisComponent */
            /** @var callable $callable */
            foreach ($tableRows as $key => $tableRow) {
                $chartRows[$key][$chartAxis][$axisComponent] = $callable($tableRow);
            }
        }

        return array_values($chartRows);
    }
}