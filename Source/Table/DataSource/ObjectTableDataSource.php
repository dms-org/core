<?php

namespace Iddigital\Cms\Core\Table\DataSource;

use Iddigital\Cms\Core\Model\Criteria\IMemberExpressionParser;
use Iddigital\Cms\Core\Model\Criteria\NestedMember;
use Iddigital\Cms\Core\Model\IObjectSet;
use Iddigital\Cms\Core\Model\IObjectSetWithLoadCriteriaSupport;
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
     * @var IMemberExpressionParser
     */
    protected $memberParser;

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
        $this->memberParser   = $objectSource->criteria()->getMemberExpressionParser();
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
        $objectSource = $this->objectSource;

        $objects          = null;
        $objectProperties = null;
        $definition       = $this->definition;

        if ($criteria) {
            $mappedCriteria = $this->criteriaMapper->mapCriteria($criteria);

            if ($criteria->getWhetherLoadsAllColumns()) {
                $objects = $objectSource->matching($mappedCriteria);
            } else {
                $supportsPartialLoad = $objectSource instanceof IObjectSetWithLoadCriteriaSupport;
                $definition          = $this->definition->forColumns($criteria->getColumnNamesToLoad());

                if ($definition->requiresObjectInstanceForMapping() || !$supportsPartialLoad) {
                    $objects = $objectSource->matching($mappedCriteria);
                } else {
                    /** @var IObjectSetWithLoadCriteriaSupport $objectSource */
                    $objectProperties = $objectSource->loadMatching($mappedCriteria);
                }
            }
        } else {
            $objects = $objectSource->getAll();
        }

        if ($objectProperties === null) {
            $objectProperties = [];

            foreach ($objects as $key => $object) {
                $objectProperties[$key] = $object->toArray();
            }

            // Load member expressions from object instances, eg 'some.nested.value'
            foreach ($this->definition->getPropertyComponentIdMap() as $memberExpression => $componentId) {
                if (strpos($memberExpression, '.') !== false || strpos($memberExpression, '(') !== null) {
                    $memberGetter = $this->memberParser->parse($this->definition->getClass(), $memberExpression)->makeArrayGetterCallable();

                    $values = $memberGetter($objects);
                    foreach ($values as $key => $value) {
                        $objectProperties[$key][$memberExpression] = $value;
                    }
                }
            }
        }

        return $this->mapObjectsToRows($definition, $objectProperties, $objects);
    }

    /**
     * @param FinalizedObjectTableDefinition $definition
     * @param array[]                        $objectProperties
     * @param ITypedObject[]|null            $objects
     *
     * @return ITableRow[]
     */
    private function mapObjectsToRows(FinalizedObjectTableDefinition $definition, array $objectProperties, array $objects = null)
    {
        $propertyComponentIdMap = $definition->getPropertyComponentIdMap();
        $componentIdCallableMap = $definition->getComponentIdCallableMap();
        $customCallables        = $definition->getCustomCallableMappers();
        $rows                   = [];

        $componentIdMap = [];

        foreach (array_merge($propertyComponentIdMap, array_keys($componentIdCallableMap)) as $componentId) {
            /** @var IColumn $column */
            /** @var IColumnComponent $component */
            list($column, $component) = $this->structure->getColumnAndComponent($componentId);

            $componentIdMap[$componentId] = [$column->getName(), $component->getName()];
        }

        foreach ($objectProperties as $key => $properties) {
            $row = [];

            foreach ($propertyComponentIdMap as $property => $componentId) {
                list($columnName, $componentName) = $componentIdMap[$componentId];
                $row[$columnName][$componentName] = $properties[$property];
            }

            if ($objects) {
                $object = $objects[$key];

                foreach ($componentIdCallableMap as $componentId => $callable) {
                    list($columnName, $componentName) = $componentIdMap[$componentId];
                    $row[$columnName][$componentName] = $callable($object);
                }

                foreach ($customCallables as $customCallable) {
                    $customCallable($row, $object);
                }
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