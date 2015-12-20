<?php

namespace Dms\Core\Table\DataSource\Criteria;

use Dms\Core\Model\Criteria\Criteria;
use Dms\Core\Model\Criteria\LoadCriteria;
use Dms\Core\Model\ICriteria;
use Dms\Core\Model\ILoadCriteria;
use Dms\Core\Table\Criteria\ColumnCriterion;
use Dms\Core\Table\DataSource\Definition\FinalizedObjectTableDefinition;
use Dms\Core\Table\IRowCriteria;
use Table\DataSource\Criteria\CriteriaMappingException;

/**
 * The row criteria mapper class that maps row criteria to an equivalent
 * object criteria.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RowCriteriaMapper
{
    /**
     * @var FinalizedObjectTableDefinition
     */
    protected $definition;

    /**
     * @var string[]
     */
    protected $componentIdPropertyMap;

    /**
     * RowCriteriaMapper constructor.
     *
     * @param FinalizedObjectTableDefinition $definition
     */
    public function __construct(FinalizedObjectTableDefinition $definition)
    {
        $this->definition             = $definition;
        $this->componentIdPropertyMap = array_flip($this->definition->getPropertyComponentIdMap());
    }

    /**
     * Maps the row criteria to the equivalent object criteria.
     * This ignores the row groupings.
     *
     * @param IRowCriteria $criteria
     *
     * @return ILoadCriteria
     */
    public function mapCriteria(IRowCriteria $criteria)
    {
        $objectCriteria = new LoadCriteria($this->definition->getClass());

        foreach ($criteria->getColumnsToLoad() as $column) {
            $objectCriteria->loadAll($this->definition->getPropertiesRequiredFor($column->getName()));
        }

        foreach ($criteria->getConditions() as $condition) {
            $objectCriteria->where(
                    $this->mapColumnToPropertyName($condition),
                    $condition->getOperator()->getOperator(),
                    $condition->getValue()
            );
        }

        foreach ($criteria->getOrderings() as $ordering) {
            $objectCriteria->orderBy(
                    $this->mapColumnToPropertyName($ordering),
                    $ordering->getDirection()
            );
        }

        $objectCriteria->skip($criteria->getRowsToSkip())->limit($criteria->getAmountOfRows());

        return $objectCriteria;
    }

    private function mapColumnToPropertyName(ColumnCriterion $condition)
    {
        $componentId = $condition->getComponentId();

        if (!isset($this->componentIdPropertyMap[$componentId])) {
            throw CriteriaMappingException::mustBeMappedToProperty($componentId);
        }

        return $this->componentIdPropertyMap[$componentId];
    }
}