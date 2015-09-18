<?php

namespace Iddigital\Cms\Core\Table\DataSource;

use Iddigital\Cms\Core\Model\IObjectSet;
use Iddigital\Cms\Core\Model\ITypedObject;
use Iddigital\Cms\Core\Table\Data\TableRow;
use Iddigital\Cms\Core\Table\DataSource\Criteria\RowCriteriaMapper;
use Iddigital\Cms\Core\Table\DataSource\Definition\FinalizedObjectTableDefinition;
use Iddigital\Cms\Core\Table\IColumn;
use Iddigital\Cms\Core\Table\IColumnComponent;
use Iddigital\Cms\Core\Table\IRowCriteria;
use Iddigital\Cms\Core\Table\ITableRow;
use Iddigital\Cms\Core\Table\ITableSection;

/**
 * The typed object table data source.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectTableDataSource extends TableDataSource
{
    /**
     * @var FinalizedObjectTableDefinition
     */
    protected $definition;

    /**
     * @var IObjectSet
     */
    protected $objectSource;

    /**
     * @var RowCriteriaMapper
     */
    protected $criteriaMapper;

    /**
     * ArrayTableDataSource constructor.
     *
     * @param FinalizedObjectTableDefinition $definition
     * @param IObjectSet                     $objectSource
     */
    public function __construct(FinalizedObjectTableDefinition $definition, IObjectSet $objectSource)
    {
        parent::__construct($definition->getStructure());

        $this->objectSource   = $objectSource;
        $this->criteriaMapper = new RowCriteriaMapper($definition);
        $this->definition     = $definition;
    }

    /**
     * @param IRowCriteria|null $criteria
     *
     * @return ITableSection[]
     */
    protected function loadRows(IRowCriteria $criteria = null)
    {
        if ($criteria) {
            $objects = $this->objectSource->matching($this->criteriaMapper->mapCriteria($criteria));
        } else {
            $objects = $this->objectSource->getAll();
        }

        return $this->mapObjectsToRows($objects);
    }

    /**
     * @param ITypedObject[] $objects
     *
     * @return ITableRow[]
     */
    private function mapObjectsToRows(array $objects)
    {
        $propertyComponentIdMap = $this->definition->getPropertyComponentIdMap();
        $componentIdCallableMap = $this->definition->getComponentIdCallableMap();
        $customCallables        = $this->definition->getCustomCallableMappers();
        $rows                   = [];

        $componentIdMap = [];

        foreach (array_merge($propertyComponentIdMap, array_keys($componentIdCallableMap)) as $componentId) {
            /** @var IColumn $column */
            /** @var IColumnComponent $component */
            list($column, $component) = $this->structure->getColumnAndComponent($componentId);

            $componentIdMap[$componentId] = [$column->getName(), $component->getName()];
        }

        foreach ($objects as $object) {
            $row              = [];
            $objectProperties = $object->toArray();

            foreach ($propertyComponentIdMap as $property => $componentId) {
                list($columnName, $componentName) = $componentIdMap[$componentId];
                $row[$columnName][$componentName] = $objectProperties[$property];
            }

            foreach ($componentIdCallableMap as $componentId => $callable) {
                list($columnName, $componentName) = $componentIdMap[$componentId];
                $row[$columnName][$componentName] = $callable($object);
            }

            foreach ($customCallables as $customCallable) {
                $customCallable($row, $object);
            }

            $rows[] = new TableRow($row);
        }

        return $rows;
    }

    /**
     * @param IRowCriteria|null $criteria
     *
     * @return int
     */
    protected function loadCount(IRowCriteria $criteria = null)
    {
        if ($criteria) {
            return $this->objectSource->countMatching($this->criteriaMapper->mapCriteria($criteria));
        } else {
            return $this->objectSource->count();
        }
    }
}