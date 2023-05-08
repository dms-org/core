<?php declare(strict_types = 1);

namespace Dms\Core\Table\DataSource;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Criteria\IMemberExpressionParser;
use Dms\Core\Model\IObjectSet;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Object\TypedObject;
use Dms\Core\Model\Object\ValueObject;
use Dms\Core\Table\Criteria\ObjectRowCriteria;
use Dms\Core\Table\Criteria\RowCriteria;
use Dms\Core\Table\Data\DataTable;
use Dms\Core\Table\Data\Object\TableRowWithObject;
use Dms\Core\Table\Data\TableSection;
use Dms\Core\Table\DataSource\Criteria\RowCriteriaMapper;
use Dms\Core\Table\DataSource\Definition\FinalizedObjectTableDefinition;
use Dms\Core\Table\IColumn;
use Dms\Core\Table\IColumnComponent;
use Dms\Core\Table\IDataTable;
use Dms\Core\Table\IRowCriteria;
use Dms\Core\Table\ITableSection;

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
        $this->criteriaMapper = new RowCriteriaMapper($definition, $objectSource);
        $this->definition     = $definition;

        $this->validateMapping();
    }

    /**
     * @inheritDoc
     */
    public function criteria() : RowCriteria
    {
        return new ObjectRowCriteria($this->structure, $this->objectSource);
    }

    /**
     * @inheritDoc
     */
    public function canUseColumnComponentInCriteria(string $componentId) : bool
    {
        /** @var IColumn $column */
        /** @var IColumnComponent $component */
        list($column, $component) = $this->structure->getColumnAndComponent($componentId);

        if (
            $component->getType()->getPhpType()->isSubsetOf(TypedObject::collectionType())
            || $component->getType()->getPhpType()->isSubsetOf(Entity::type())
            || $component->getType()->getPhpType()->isSubsetOf(ValueObject::type())
        ) return false;

        return in_array($column->getName() . '.' . $component->getName(), $this->definition->getPropertyComponentIdMap(), true);
    }

    protected function validateMapping()
    {
        foreach ($this->definition->getPropertyComponentIdMap() as $memberExpression => $componentId) {
            $member = $this->memberParser->parse($this->definition->getClass(), $memberExpression);

            $columnType = $this->structure->getComponent($componentId)->getType()->getPhpType()->nullable();
            $memberType = $member->getResultingType();

            if (!$columnType->isSupersetOf($memberType)) {
                throw InvalidArgumentException::format(
                    'Invalid object property to column mapping: could not map member \'%s\' of type %s on class %s to column component \'%s\' of type %s as the types are incompatible',
                    $memberExpression, $memberType->asTypeString(), $this->definition->getClass()->getClassName(),
                    $componentId, $columnType->asTypeString()
                );
            }
        }
    }

    /**
     * @return IObjectSet
     */
    final public function getObjectDataSource() : IObjectSet
    {
        return $this->objectSource;
    }

    /**
     * @return FinalizedObjectTableDefinition
     */
    final public function getDefinition() : FinalizedObjectTableDefinition
    {
        return $this->definition;
    }

    /**
     * @param IRowCriteria|null $criteria
     *
     * @return ITableSection[]
     */
    protected function loadRows(IRowCriteria $criteria = null) : array
    {
        $objectSource = $this->objectSource;

        $objects          = null;
        $objectProperties = null;
        $definition       = $this->definition;

        if ($criteria) {
            $mappedCriteria = $this->criteriaMapper->mapCriteria($criteria);
            $objects        = $objectSource->matching($mappedCriteria);
        } else {
            $objects = $objectSource->getAll();
        }

        if ($objectProperties === null) {
            $objectProperties = $this->loadObjectProperties($objects);
        }

        return $this->mapObjectsToRows($definition, $objectProperties, $objects, $criteria);
    }

    /**
     * @param object[] $objects
     *
     * @return IDataTable
     */
    public function loadFromObjects(array $objects) : IDataTable
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'objects', $objects, $this->definition->getClass()->getClassName());
        $objectProperties = $this->loadObjectProperties($objects);

        $rows = $this->mapObjectsToRows($this->definition, $objectProperties, $objects);

        return new DataTable($this->structure, [new TableSection($this->structure, null, $rows)]);
    }

    /**
     * @param TypedObject[] $objects
     *
     * @return array
     */
    protected function loadObjectProperties(array $objects) : array
    {
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

        return $objectProperties;
    }

    /**
     * @param FinalizedObjectTableDefinition $definition
     * @param array[]                        $objectProperties
     * @param ITypedObject[]                 $objects
     * @param IRowCriteria|null              $criteria
     *
     * @return array|\Dms\Core\Table\ITableRow[]
     */
    private function mapObjectsToRows(
        FinalizedObjectTableDefinition $definition,
        array $objectProperties,
        array $objects,
        IRowCriteria $criteria = null
    ) : array
    {
        $propertyComponentIdMap = $definition->getPropertyComponentIdMap();
        $indexComponentIdMap    = $definition->getIndexComponentIdMap();
        $componentIdCallableMap = $definition->getComponentIdCallableMap();
        $customCallables        = $definition->getCustomCallableMappers();

        if ($criteria) {
            $componentIdsToLoad = [];

            foreach ($criteria->getColumnsToLoad() as $column) {
                foreach ($column->getComponents() as $component) {
                    $componentIdsToLoad[] = $column->getComponentId($component->getName());
                }
            }
            
            $propertyComponentIdMap = array_intersect($propertyComponentIdMap, $componentIdsToLoad);
            $indexComponentIdMap    = array_intersect($indexComponentIdMap, $componentIdsToLoad);
            $componentIdCallableMap = array_intersect_key($componentIdCallableMap, array_flip($componentIdsToLoad));
        }

        $rows = [];

        $componentIdMap = [];

        $allComponentIds = array_merge($propertyComponentIdMap, $indexComponentIdMap, array_keys($componentIdCallableMap));
        foreach ($allComponentIds as $componentId) {
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

            foreach ($indexComponentIdMap as $componentId) {
                list($columnName, $componentName) = $componentIdMap[$componentId];
                $row[$columnName][$componentName] = $key;
            }

            $object = $objects[$key];

            foreach ($componentIdCallableMap as $componentId => $callable) {
                list($columnName, $componentName) = $componentIdMap[$componentId];
                $row[$columnName][$componentName] = $callable($object);
            }

            foreach ($customCallables as $customCallable) {
                $customCallable($row, $object);
            }

            $rows[] = new TableRowWithObject($row, $object);
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
        if ($criteria) {
            return $this->objectSource->countMatching($this->criteriaMapper->mapCriteria($criteria));
        } else {
            return $this->objectSource->count();
        }
    }
}