<?php

namespace Iddigital\Cms\Core\Table\Chart\DataSource\Criteria;

use Iddigital\Cms\Core\Table\Chart\DataSource\Definition\FinalizedChartTableMapperDefinition;
use Iddigital\Cms\Core\Table\Chart\IChartCriteria;
use Iddigital\Cms\Core\Table\IRowCriteria;

/**
 * The chart-to-table criteria mapper class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartTableCriteriaMapper
{
    /**
     * @var FinalizedChartTableMapperDefinition
     */
    protected $definition;


    /**
     * @var string[]
     */
    protected $chartToTableComponentMap;

    /**
     * ChartTableCriteriaMapper constructor.
     *
     * @param FinalizedChartTableMapperDefinition $definition
     */
    public function __construct(FinalizedChartTableMapperDefinition $definition)
    {
        $this->definition               = $definition;
        $this->chartToTableComponentMap = array_flip($this->definition->getTableToChartComponentIdMap());
    }

    /**
     * @param IChartCriteria $chartCriteria
     *
     * @return IRowCriteria
     */
    public function mapCriteria(IChartCriteria $chartCriteria)
    {
        $rowCriteria = $this->definition->getTableDataSource()->criteria();

        $rowCriteria->loadAll($this->definition->getTableColumnNamesToLoad());

        foreach ($chartCriteria->getConditions() as $condition) {
            $axis     = $condition->getAxis();
            $axisName = $axis->getName();
            $operator = $condition->getOperator()->getOperator();
            $value    = $condition->getValue();

            foreach ($axis->getComponents() as $component) {
                $chartComponentId = $axisName . '.' . $component->getName();
                $tableComponentId = $this->getTableComponentIdFromChartComponentId($chartComponentId);
                $rowCriteria->where($tableComponentId, $operator, $value);
            }
        }

        foreach ($chartCriteria->getOrderings() as $ordering) {
            $axis      = $ordering->getAxis();
            $axisName  = $axis->getName();
            $direction = $ordering->getDirection();

            foreach ($axis->getComponents() as $component) {
                $chartComponentId = $axisName . '.' . $component->getName();
                $tableComponentId = $this->getTableComponentIdFromChartComponentId($chartComponentId);
                $rowCriteria->orderBy($tableComponentId, $direction);
            }
        }

        return $rowCriteria;
    }

    /**
     * @param $chartComponentId
     *
     * @return string
     * @throws CriteriaMappingException
     */
    protected function getTableComponentIdFromChartComponentId($chartComponentId)
    {
        if (!isset($this->chartToTableComponentMap[$chartComponentId])) {
            throw CriteriaMappingException::mustBeMappedToColumn($chartComponentId);
        }

        return $this->chartToTableComponentMap[$chartComponentId];
    }
}