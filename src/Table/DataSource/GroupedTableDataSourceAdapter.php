<?php declare(strict_types = 1);

namespace Dms\Core\Table\DataSource;

use Dms\Core\Table\Data\TableRow;
use Dms\Core\Table\DataSource\Definition\FinalizedGroupedTableDefinition;
use Dms\Core\Table\IRowCriteria;
use Dms\Core\Table\ITableRow;

/**
 * The grouped table data source.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class GroupedTableDataSourceAdapter extends TableDataSource
{
    /**
     * @var FinalizedGroupedTableDefinition
     */
    protected $definition;

    /**
     * GroupedTableDataSourceAdapter constructor.
     *
     * @param FinalizedGroupedTableDefinition $definition
     */
    public function __construct(FinalizedGroupedTableDefinition $definition)
    {
        parent::__construct($definition->getStructure());
        $this->definition = $definition;
    }

    /**
     * Returns whether the supplied component can be used within row criteria
     *
     * @param string $componentId
     *
     * @return bool
     */
    public function canUseColumnComponentInCriteria(string $componentId) : bool
    {
        return $this->definition->getDataSource()->getStructure()->hasComponent($componentId);
    }

    /**
     * @param IRowCriteria|null $criteria
     *
     * @return ITableRow[]
     */
    protected function loadRows(IRowCriteria $criteria = null) : array
    {
        $criteria = $criteria ?? $this->criteria()->loadAll();

        $componentIdCallableMap = $this->definition->getComponentIdCallableMap();
        $dataSourceCriteria     = $this->definition->getDataSource()->criteria()->loadAll();
        $groupedByComponentIds  = array_fill_keys($this->definition->getGroupByComponentIds(), true);

        foreach ($criteria->getConditionGroups() as $group) {
            $dataSourceCriteria->setConditionMode($group->getConditionMode());

            foreach ($group->getConditions() as $condition) {
                if (isset($groupedByComponentIds[$condition->getComponentId()])) {
                    $dataSourceCriteria->where(
                        $condition->getComponentId(),
                        $condition->getOperator()->getOperator(),
                        $condition->getValue(),
                        true
                    );
                }
            }
        }


        foreach ($criteria->getOrderings() as $ordering) {
            if (isset($groupedByComponentIds[$ordering->getComponentId()])) {
                $dataSourceCriteria->orderBy(
                    $ordering->getComponentId(),
                    $ordering->getDirection()
                );
            }
        }

        foreach ($groupedByComponentIds as $componentId => $placeholder) {
            $dataSourceCriteria->groupBy($componentId);
        }

        $dataTable = $this->definition->getDataSource()->load($dataSourceCriteria);
        $rows      = [];

        foreach ($dataTable->getSections() as $section) {
            $rowData   = [];
            $groupData = $section->getGroupData();

            foreach ($groupedByComponentIds as $componentId => $placeholder) {
                list($column, $component) = explode('.', $componentId);
                $rowData[$column][$component] = $groupData[$componentId];
            }

            foreach ($componentIdCallableMap as $componentId => $callable) {
                list($column, $component) = explode('.', $componentId);
                $rowData[$column][$component] = $callable($section->getRowArray(), $groupData);
            }

            $rows[] = new TableRow($rowData);
        }

        return $rows;
    }

    /**
     * @param IRowCriteria|null $criteria
     *
     * @return int
     */
    protected function loadCount(IRowCriteria $criteria = null) : int
    {
        return count($this->loadRows($criteria));
    }
}