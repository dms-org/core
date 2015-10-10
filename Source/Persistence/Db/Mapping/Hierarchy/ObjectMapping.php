<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Hierarchy;

use Iddigital\Cms\Core\Model\ITypedObject;
use Iddigital\Cms\Core\Persistence\Db\Exception\InvalidRowException;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation\IAccessor;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation\RelationMapping;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ParentChildMap;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ParentChildrenMap;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\EntityRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IEmbeddedToOneRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IToManyRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IToOneRelation;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Query;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The object mapping base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ObjectMapping implements IObjectMapping
{
    /**
     * @var FinalizedMapperDefinition
     */
    protected $definition;

    /**
     * @var string
     */
    protected $primaryKeyColumnName;

    /**
     * @var string
     */
    protected $objectType;

    /**
     * @var IObjectMapping[]
     */
    protected $subClassMappings = [];

    /**
     * @var ITypedObject|null
     */
    protected $cleanInstance = null;

    /**
     * @var string
     */
    protected $dependencyMode;

    /**
     * @var Table[]
     */
    protected $mappingTables;

    /**
     * @var string[]
     */
    protected $specificColumnsToLoad;

    /**
     * @var string[]
     */
    protected $allColumnsToLoad = [];

    /**
     * ObjectMapping constructor.
     *
     * @param FinalizedMapperDefinition $definition
     * @param string                    $dependencyMode
     */
    public function __construct(FinalizedMapperDefinition $definition, $dependencyMode)
    {
        $this->dependencyMode = $dependencyMode;
        $this->loadFromDefinition($definition);

        if (!$definition->isForAbstractClass()) {
            $this->cleanInstance = $definition->getClass()->newCleanInstance();
        }
    }

    protected function loadFromDefinition(FinalizedMapperDefinition $definition)
    {
        $this->definition            = $definition;
        $this->objectType            = $definition->getClassName();
        $this->subClassMappings      = $definition->getSubClassMappings();
        $this->primaryKeyColumnName  = $definition->getTable()->getPrimaryKeyColumnName();
        $this->mappingTables         = $this->loadMappingTables($definition);
        $this->specificColumnsToLoad = $this->loadRequiredColumns($definition);
        $this->specificColumnsToLoad = array_merge($this->specificColumnsToLoad, $this->findAllColumnsToLoad());
        $this->allColumnsToLoad      = array_merge($this->specificColumnsToLoad, $this->findAllSubclassColumnsToLoad());
    }

    protected function loadMappingTables(FinalizedMapperDefinition $definition)
    {
        return [];
    }

    protected function loadRequiredColumns(FinalizedMapperDefinition $definition)
    {
        return [];
    }

    /**
     * @param IObjectMapper $parentMapper
     */
    public function initializeRelations(IObjectMapper $parentMapper)
    {
        $this->definition->initializeRelations($parentMapper);

        $this->loadFromDefinition($this->definition);
    }

    /**
     * @return string[]
     */
    protected function findAllColumnsToLoad()
    {
        $columns = [];

        foreach ($this->definition->getPropertyColumnMap() as $column) {
            $columns[] = $column;
        }

        foreach ($this->definition->getMethodColumnMap() as $column) {
            $columns[] = $column;
        }

        foreach ($this->definition->getColumnSetterMap() as $column => $callable) {
            $columns[] = $column;
        }

        foreach ($this->definition->getRelationMappings() as $relationMapping) {
            foreach ($relationMapping->getRelation()->getParentColumnsToLoad() as $column) {
                $columns[] = $column;
            }
        }

        if ($this->hasEntityRelations() && $this->primaryKeyColumnName) {
            $columns[] = $this->primaryKeyColumnName;
        }

        foreach ($this->definition->getLockingStrategies() as $lockingStrategy) {
            foreach ($lockingStrategy->getLockingColumnNames() as $column) {
                $columns[] = $column;
            }
        }

        return array_unique($columns, SORT_STRING);
    }

    protected function findAllSubclassColumnsToLoad()
    {
        $columns = [];

        foreach ($this->subClassMappings as $mapping) {
            if ($mapping instanceof EmbeddedObjectMapping) {
                foreach ($mapping->getAllColumnsToLoad() as $column) {
                    $columns[] = $column;
                }
            }
        }

        return array_unique($columns, SORT_STRING);
    }

    /**
     * @return bool
     */
    final protected function hasEntityRelations()
    {
        foreach ($this->definition->getRelationMappings() as $relation) {
            if ($relation->getRelation() instanceof EntityRelation) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    final public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * {@inheritDoc}
     */
    final public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * {@inheritDoc}
     */
    final public function getDependencyMode()
    {
        return $this->dependencyMode;
    }

    /**
     * {@inheritDoc}
     */
    final public function getMappingTables()
    {
        $tables = $this->mappingTables;

        foreach ($this->subClassMappings as $mapping) {
            foreach ($mapping->getMappingTables() as $table) {
                $tables[] = $table;
            }
        }

        return $tables;
    }

    /**
     * {@inheritDoc}
     */
    public function withEmbeddedColumnsPrefixedBy($prefix)
    {
        $clone                       = clone $this;
        $clone->definition           = $this->definition->withColumnsPrefixedBy($prefix);
        $clone->subClassMappings     = $clone->definition->getSubClassMappings();
        $clone->primaryKeyColumnName = $prefix . $clone->primaryKeyColumnName;

        foreach ($clone->specificColumnsToLoad as $key => $column) {
            $clone->specificColumnsToLoad[$key] = $prefix . $column;
        }

        foreach ($clone->allColumnsToLoad as $key => $column) {
            $clone->allColumnsToLoad[$key] = $prefix . $column;
        }

        $clone->loadFromDefinition($clone->definition);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    final public function addLoadToSelect(Select $select)
    {
        $this->addLoadClausesToSelect($select);

        foreach ($this->subClassMappings as $subType) {
            $subType->addLoadToSelect($select);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addSpecificLoadToQuery(Query $query, $objectType)
    {
        foreach ($this->subClassMappings as $subType) {
            if (is_a($objectType, $subType->getObjectType(), true)) {
                $subType->addSpecificLoadToQuery($query, $objectType);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function getClassConditionExpr(Query $query, $objectType)
    {
        foreach ($this->subClassMappings as $subType) {
            if (is_a($objectType, $subType->getObjectType(), true)) {
                return $subType->getClassConditionExpr($query, $objectType);
            }
        }

        return $this->makeClassConditionExpr($query);
    }

    /**
     * @param Query $query
     *
     * @return Expr
     */
    abstract protected function makeClassConditionExpr(Query $query);

    /**
     * @param Select $select
     *
     * @return void
     */
    protected function addLoadClausesToSelect(Select $select)
    {
        foreach ($this->getAllColumnsToLoad() as $column) {
            $select->addRawColumn($column);
        }
    }

    /**
     * @return string[]
     */
    final public function getAllColumnsToLoad()
    {
        return $this->allColumnsToLoad;
    }

    /**
     * {@inheritdoc}
     */
    final public function constructNewObjectFromRow(Row $row)
    {
        $subClassRow = $this->processRowBeforeLoadSubclass($row);
        foreach ($this->subClassMappings as $subType) {
            if ($subType->rowMatchesType($subClassRow)) {
                return $subType->constructNewObjectFromRow($subClassRow);
            }
        }

        if (!$this->cleanInstance || !$this->rowMatchesType($row)) {
            throw new InvalidRowException($row, 'Row data does not match any given subclass mappings');
        }

        return clone $this->cleanInstance;
    }

    protected function processRowBeforeLoadSubclass(Row $row)
    {
        return $row;
    }


    /**
     * {@inheritdoc}
     */
    final public function rowMatchesType(Row $row)
    {
        foreach ($this->subClassMappings as $subType) {
            if ($subType->rowMatchesType($row)) {
                return true;
            }
        }

        return $this->rowMatchesObjectType($row);
    }

    /**
     * @param Row $row
     *
     * @return bool
     */
    abstract protected function rowMatchesObjectType(Row $row);

    /**
     * @param LoadingContext $context
     * @param ITypedObject[] $objects
     * @param Row[]          $rows
     */
    public function loadAll(LoadingContext $context, array $objects, array $rows)
    {
        $objectProperties = $this->loadAllProperties($context, $rows, $objects);

        foreach ($objects as $key => $object) {
            $object->hydrate($objectProperties[$key]);
        }
    }

    /**
     * @param LoadingContext $context
     * @param Row[]          $rows
     * @param ITypedObject[] $objects
     *
     * @return array[]
     */
    public function loadAllProperties(LoadingContext $context, array $rows, array $objects)
    {
        /** @var Row[] $rows */
        foreach ($rows as $key => $row) {
            $rows[$key] = $this->processRowBeforeLoadSubclass($row);
        }

        $definition = $this->definition;

        $propertyMap         = $definition->getPropertyColumnMap();
        $dbToPhpConverterMap = $definition->getDbToPhpPropertyConverterMap();
        $columnSetterMap     = $this->definition->getColumnSetterMap();

        $objectProperties = [];
        $columnDataArray  = [];

        foreach ($rows as $key => $object) {
            $objectProperties[$key] = [];
            $columnDataArray[$key]  = $rows[$key]->getColumnData();
        }

        foreach ($propertyMap as $property => $column) {

            if (isset($dbToPhpConverterMap[$property])) {
                $converter = $dbToPhpConverterMap[$property];
                foreach ($objectProperties as $key => &$properties) {
                    $properties[$property] = $converter($columnDataArray[$key][$column], $columnDataArray[$key]);
                }
            } else {
                foreach ($objectProperties as $key => &$properties) {
                    $properties[$property] = $columnDataArray[$key][$column];
                }
            }
            unset($properties);
        }

        foreach ($columnSetterMap as $column => $setterCallback) {
            foreach ($objects as $key => $object) {
                $setterCallback($object, $rows[$key]->getColumn($column));
            }
        }

        $this->loadRelations($context, $rows, $objects, $objectProperties);

        /** @var IObjectMapping $mapping */
        foreach ($this->subClassMappings as $subClass => $mapping) {
            list($objectPropertiesOfSubClass, $rowsForSubClass) = $this->loadPropertiesForSubclass($mapping, $rows, $objectProperties);

            if (!$objectPropertiesOfSubClass) {
                continue;
            }

            foreach ($mapping->loadAllProperties($context, $rowsForSubClass, $objects) as $key => $subClassProperties) {
                $objectProperties[$key] += $subClassProperties;
            }
        }

        return $objectProperties;
    }

    /**
     * @param LoadingContext $context
     * @param Row[]          $rows
     * @param ITypedObject[] $objects
     * @param array[]        $objectProperties
     *
     * @return void
     */
    private function loadRelations(
            LoadingContext $context,
            array $rows,
            array $objects,
            array &$objectProperties
    ) {
        foreach ($this->definition->getToOneRelationMappings() as $relationMapping) {
            $relation = $relationMapping->getRelation();
            $accessor = $relationMapping->getAccessor();

            $map       = new ParentChildMap($this->primaryKeyColumnName);
            $rowKeyMap = new \SplObjectStorage();

            foreach ($rows as $key => $row) {
                $map->add($row, null);
                $rowKeyMap[$row] = $key;
            }

            $relation->load($context, $map);

            foreach ($map->getItems() as $item) {
                $objectKey = $rowKeyMap[$item->getParent()];
                $accessor->set($objects[$objectKey], $objectProperties[$objectKey], $item->getChild());
            }
        }

        foreach ($this->definition->getToManyRelationMappings() as $relationMapping) {
            $relation = $relationMapping->getRelation();
            $accessor = $relationMapping->getAccessor();

            $map       = new ParentChildrenMap($this->primaryKeyColumnName);
            $rowKeyMap = new \SplObjectStorage();

            foreach ($rows as $key => $row) {
                $map->add($row, []);
                $rowKeyMap[$row] = $key;
            }

            $relation->load($context, $map);

            foreach ($map->getItems() as $item) {
                $objectKey = $rowKeyMap[$item->getParent()];

                $accessor->set($objects[$objectKey], $objectProperties[$objectKey], $relation->buildCollection($item->getChildren()));
            }
        }
    }

    /**
     * @param PersistenceContext $context
     * @param ITypedObject[]     $objects
     * @param Row[]              $rows
     * @param array|null         $extraData
     */
    public function persistAll(
            PersistenceContext $context,
            array $objects,
            array $rows,
            array $extraData = null
    ) {
        $objectProperties = $this->persistObjectDataToRows($objects, $rows);

        $this->performLockingOperations($context, $objects, $rows);

        $this->performPrePersist($context, $objects, $rows, $objectProperties);

        $this->performPersist($context, $rows, $extraData);

        $this->performPostPersist($context, $objects, $rows, $objectProperties);
    }

    protected function performLockingOperations(PersistenceContext $context, array $objects, array $rows)
    {
        foreach ($this->definition->getLockingStrategies() as $lockingStrategy) {
            $lockingStrategy->applyLockingDataBeforeCommit($context, $objects, $rows);

            $context->afterCommit(function () use ($lockingStrategy, $context, $objects, $rows) {
                $lockingStrategy->applyLockingDataAfterCommit($context, $objects, $rows);
            });
        }
    }

    protected function performPrePersist(PersistenceContext $context, array $objects, array $rows, $objectProperties)
    {
        $this->persistRelations($context, IRelation::DEPENDENT_PARENTS, $rows, $objects, $objectProperties);
        $this->persistSubclasses($context, IRelation::DEPENDENT_PARENTS, $objects, $rows);
        $this->persistEmbeddedRelationsBeforeParent($context, $rows, $objects, $objectProperties);
        $this->persistEmbeddedSubclassesBeforeParent($context, $objects, $rows);
    }

    /**
     * @param PersistenceContext $context
     * @param Row[]              $rows
     * @param array|null         $extraData
     *
     * @return void
     */
    abstract protected function performPersist(PersistenceContext $context, array $rows, array $extraData = null);

    protected function performPostPersist(PersistenceContext $context, array $objects, array $rows, array $objectProperties)
    {
        $this->persistRelations($context, IRelation::DEPENDENT_CHILDREN, $rows, $objects, $objectProperties);
        $this->persistSubclasses($context, IRelation::DEPENDENT_CHILDREN, $objects, $rows);
        $this->persistEmbeddedRelationsAfterParent($context, $rows, $objects, $objectProperties);
        $this->persistEmbeddedSubclassesAfterParent($context, $objects, $rows);
    }

    /**
     * @param ITypedObject[] $objects
     *
     * @return array[]
     */
    protected function getObjectProperties(array $objects)
    {
        $objectProperties = [];
        /** @var ITypedObject[] $objects */
        foreach ($objects as $key => $object) {
            $objectProperties[$key] = $object->toArray();
        }

        return $objectProperties;
    }

    final protected function persistObjectDataToRows(array $objects, array $rows)
    {
        $objectProperties = $this->getObjectProperties($objects);

        $definition = $this->definition;
        /** @var ITypedObject[] $objects */
        /** @var Row[] $rows */

        $propertyMap         = $definition->getPropertyColumnMap();
        $phpToDbConverterMap = $definition->getPhpToDbPropertyConverterMap();

        foreach ($propertyMap as $property => $column) {
            if (isset($phpToDbConverterMap[$property])) {
                $converter = $phpToDbConverterMap[$property];

                foreach ($objectProperties as $key => $properties) {
                    $rows[$key]->setColumn($column, $converter($properties[$property], $properties));
                }
            } else {
                foreach ($objectProperties as $key => $properties) {
                    $rows[$key]->setColumn($column, $properties[$property]);
                }
            }
        }

        foreach ($definition->getColumnGetterMap() as $column => $getterCallback) {
            foreach ($objects as $key => $object) {
                $rows[$key]->setColumn($column, $getterCallback($object));
            }
        }

        foreach ($definition->getMethodColumnMap() as $method => $column) {
            foreach ($objects as $key => $object) {
                $rows[$key]->setColumn($column, $object->{$method}());
            }
        }

        return $objectProperties;
    }

    final protected function loadParentChildMap(array $rows, array $objects, array $objectProperties, IAccessor $accessor)
    {
        $map = new ParentChildMap($this->primaryKeyColumnName);

        foreach ($objectProperties as $key => $properties) {
            $map->add($rows[$key], $accessor->get($objects[$key], $properties));
        }

        return $map;
    }

    final protected function persistRelations(PersistenceContext $context, $dependencyMode, array $rows, array $objects, array $objectProperties)
    {
        $definition = $this->definition;

        foreach ($definition->getRelationMappingsWith($dependencyMode) as $relationMapping) {
            $relation = $relationMapping->getRelation();
            $accessor = $relationMapping->getAccessor();

            if ($context->isRelationIgnored($relation)) {
                continue;
            }

            if ($relation instanceof IToOneRelation) {
                $map = $this->loadParentChildMap($rows, $objects, $objectProperties, $accessor);

                $relation->persist($context, $map);

            } elseif ($relation instanceof IToManyRelation) {
                $map = new ParentChildrenMap($this->primaryKeyColumnName);

                foreach ($objectProperties as $key => $properties) {
                    $map->add($rows[$key], iterator_to_array($accessor->get($objects[$key], $properties)));
                }

                $relation->persist($context, $map);
            }
        }
    }

    final protected function getRelationsToPersist(PersistenceContext $context)
    {
        /** @var RelationMapping[] $relationMappings */
        $relationMappings = [];

        foreach ($this->definition->getRelationMappings() as $relationMapping) {
            $relation = $relationMapping->getRelation();

            if ($context->isRelationIgnored($relation)) {
                continue;
            }

            $relationMappings[] = $relationMapping;
        }

        return $relationMappings;
    }

    final protected function persistEmbeddedRelationsBeforeParent(PersistenceContext $context, array $rows, array $objects, array $objectProperties)
    {
        foreach ($this->getRelationsToPersist($context) as $relationMapping) {
            $relation = $relationMapping->getRelation();
            $accessor = $relationMapping->getAccessor();

            if ($relation instanceof IEmbeddedToOneRelation) {
                $map = $this->loadParentChildMap($rows, $objects, $objectProperties, $accessor);

                $relation->persistBeforeParent($context, $map);
            }
        }
    }

    final protected function persistEmbeddedRelationsAfterParent(PersistenceContext $context, array $rows, array $objects, array $objectProperties)
    {
        foreach ($this->getRelationsToPersist($context) as $relationMapping) {
            $relation = $relationMapping->getRelation();
            $accessor = $relationMapping->getAccessor();

            if ($relation instanceof IEmbeddedToOneRelation) {
                $map = $this->loadParentChildMap($rows, $objects, $objectProperties, $accessor);

                $relation->persistAfterParent($context, $map);
            }
        }
    }

    final protected function persistSubclasses(PersistenceContext $context, $dependencyMode, array $objects, array $rows)
    {
        $definition = $this->definition;

        /** @var IObjectMapping $mapping */
        foreach ($definition->getSubClassMappingsWith($dependencyMode) as $mapping) {
            list($objectsOfSubClass, $rowsForSubClass) = $this->loadInstancesForSubclass($mapping, $rows, $objects);

            if (!$objectsOfSubClass) {
                continue;
            }

            $mapping->persistAll($context, $objectsOfSubClass, $rowsForSubClass);
        }
    }

    final protected function persistEmbeddedSubclassesBeforeParent(PersistenceContext $context, array $objects, array $rows)
    {
        foreach ($this->subClassMappings as $mapping) {
            if ($mapping instanceof IEmbeddedObjectMapping) {
                $mapping->persistAllBeforeParent($context, $objects, $rows);
            }
        }
    }

    final protected function persistEmbeddedSubclassesAfterParent(PersistenceContext $context, array $objects, array $rows)
    {
        foreach ($this->subClassMappings as $mapping) {
            if ($mapping instanceof IEmbeddedObjectMapping) {
                $mapping->persistAllAfterParent($context, $objects, $rows);
            }
        }
    }

    public function delete(PersistenceContext $context, Delete $deleteQuery, $dependencyMode = null)
    {
        $this->performPreDelete($context, $deleteQuery);

        $this->performDelete($context, $deleteQuery);

        $this->performPostDelete($context, $deleteQuery);
    }


    protected function performPreDelete(PersistenceContext $context, Delete $deleteQuery)
    {
        $this->deleteRelations($context, IRelation::DEPENDENT_CHILDREN, $deleteQuery);
        $this->deleteSubclasses($context, IRelation::DEPENDENT_CHILDREN, $deleteQuery);
        $this->deleteEmbeddedRelationsBeforeParent($context, $deleteQuery);
        $this->deleteEmbeddedSubclassesBeforeParent($context, $deleteQuery);
    }

    /**
     * @param PersistenceContext $context
     * @param Delete             $deleteQuery
     *
     * @return void
     */
    abstract protected function performDelete(PersistenceContext $context, Delete $deleteQuery);

    protected function performPostDelete(PersistenceContext $context, Delete $deleteQuery)
    {
        $this->deleteRelations($context, IRelation::DEPENDENT_PARENTS, $deleteQuery);
        $this->deleteSubclasses($context, IRelation::DEPENDENT_PARENTS, $deleteQuery);
        $this->deleteEmbeddedRelationsAfterParent($context, $deleteQuery);
        $this->deleteEmbeddedSubclassesAfterParent($context, $deleteQuery);
    }

    final protected function deleteRelations(PersistenceContext $context, $dependencyMode, Delete $parentDelete)
    {
        $definition = $this->definition;

        foreach ($definition->getRelationMappingsWith($dependencyMode) as $relationMapping) {
            $relationMapping->getRelation()->delete($context, $parentDelete);
        }
    }

    final protected function deleteEmbeddedRelationsBeforeParent(PersistenceContext $context, Delete $parentDelete)
    {
        foreach ($this->definition->getRelationMappings() as $relationMapping) {
            $relation = $relationMapping->getRelation();

            if ($relation instanceof IEmbeddedToOneRelation) {
                $relation->deleteBeforeParent($context, $parentDelete);
            }
        }
    }

    final protected function deleteEmbeddedRelationsAfterParent(PersistenceContext $context, Delete $parentDelete)
    {
        foreach ($this->definition->getRelationMappings() as $relationMapping) {
            $relation = $relationMapping->getRelation();

            if ($relation instanceof IEmbeddedToOneRelation) {
                $relation->deleteAfterParent($context, $parentDelete);
            }
        }
    }

    final protected function deleteSubclasses(PersistenceContext $context, $dependencyMode, Delete $parentDelete)
    {
        $definition = $this->definition;

        /** @var IObjectMapping $mapping */
        foreach ($definition->getSubClassMappingsWith($dependencyMode) as $mapping) {
            $mapping->delete($context, $parentDelete);
        }
    }

    final protected function deleteEmbeddedSubclassesBeforeParent(PersistenceContext $context, Delete $parentDelete)
    {
        foreach ($this->subClassMappings as $mapping) {
            if ($mapping instanceof IEmbeddedObjectMapping) {
                $mapping->deleteBeforeParent($context, $parentDelete);
            }
        }
    }

    final protected function deleteEmbeddedSubclassesAfterParent(PersistenceContext $context, Delete $parentDelete)
    {
        foreach ($this->subClassMappings as $mapping) {
            if ($mapping instanceof IEmbeddedObjectMapping) {
                $mapping->deleteAfterParent($context, $parentDelete);
            }
        }
    }

    /**
     * @param IObjectMapping $mapping
     * @param Row[]          $rows
     * @param ITypedObject[] $objects
     *
     * @return array
     */
    protected function loadInstancesForSubclass(IObjectMapping $mapping, array $rows, array $objects)
    {
        $subClass          = $mapping->getObjectType();
        $objectsOfSubClass = [];
        $rowsForSubClass   = [];

        foreach ($objects as $key => $object) {
            if ($object instanceof $subClass) {
                $objectsOfSubClass[$key] = $object;
                $rowsForSubClass[$key]   = $rows[$key];
            }
        }

        return array($objectsOfSubClass, $rowsForSubClass);
    }

    /**
     * @param IObjectMapping $mapping
     * @param Row[]          $rows
     * @param array[]        $objectProperties
     *
     * @return array
     */
    protected function loadPropertiesForSubclass(IObjectMapping $mapping, array $rows, array $objectProperties)
    {
        $objectsPropertiesOfSubClass = [];
        $rowsForSubClass             = [];

        foreach ($rows as $key => $row) {
            if ($mapping->rowMatchesType($row)) {
                $objectsPropertiesOfSubClass[$key] = $objectProperties[$key];
                $rowsForSubClass[$key]             = $row;
            }
        }

        return array($objectsPropertiesOfSubClass, $rowsForSubClass);
    }
}