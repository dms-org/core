<?php

namespace Dms\Core\Persistence\Db\Mapping;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Model\ITypedCollection;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Model\Object\TypedObject;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Dms\Core\Persistence\Db\Mapping\Hierarchy\ParentObjectMapping;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Row;

/**
 * The object mapper base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ObjectMapper implements IObjectMapper
{
    /**
     * @var bool
     */
    protected $hasInitializedRelations = false;

    /**
     * @var ParentObjectMapping
     */
    protected $mapping;

    /**
     * @var string
     */
    private $objectType;

    /**
     * ObjectMapping constructor.
     *
     * @param FinalizedMapperDefinition $definition
     *
     * @throws \Dms\Core\Persistence\Db\Mapping\Definition\IncompleteMapperDefinitionException
     */
    public function __construct(FinalizedMapperDefinition $definition)
    {
        $this->objectType = $definition->getClassName();
        $this->mapping    = $this->loadMapping($definition);

        $this->loadFromDefinition($definition);
    }

    /**
     * @inheritDoc
     */
    final public function initializeRelations()
    {
        if ($this->hasInitializedRelations) {
            return;
        }

        $this->mapping->initializeRelations($this);
        $this->loadFromDefinition($this->getDefinition());
        $this->hasInitializedRelations = true;
    }

    /**
     * @param FinalizedMapperDefinition $definition
     *
     * @return ParentObjectMapping
     */
    abstract protected function loadMapping(FinalizedMapperDefinition $definition);

    abstract protected function loadFromDefinition(FinalizedMapperDefinition $definition);

    /**
     * {@inheritDoc}
     */
    final public function getDefinition()
    {
        if (!$this->mapping) {
            throw new InvalidOperationException('Mapping not defined yet');
        }

        return $this->mapping->getDefinition();
    }

    /**
     * {@inheritDoc}
     */
    final public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @inheritDoc
     */
    public function buildCollection(array $objects)
    {
        /** @var string|TypedObject $objectType */
        $objectType = $this->objectType;

        return $objectType::collection($objects);
    }

    /**
     * @inheritDoc
     */
    final public function getMapperHash()
    {
        return spl_object_hash($this);
    }

    /**
     * @return ParentObjectMapping
     */
    final public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * @inheritDoc
     */
    final public function getNestedMappers()
    {
        $mappers = [];
        $this->findMappers($mappers);

        return $mappers;
    }

    /**
     * @inheritDoc
     */
    public function findMapperFor($class)
    {
        /** @var IObjectMapper[] $mappers */
        $mappers = array_merge([$this], $this->getNestedMappers());

        foreach ($mappers as $mapper) {
            if (is_a($class, $mapper->getObjectType(), true)) {
                return $mapper;
            }
        }

        return null;
    }


    final protected function findMappers(array &$mappers)
    {
        if (!$this->getDefinition()) {
            throw new InvalidOperationException('Mapper has not been defined yet');
        }

        $relationMappings = $this->getDefinition()->getRelationMappings();

        foreach ($this->getDefinition()->getSubClassMappings() as $mapping) {
            foreach ($mapping->getDefinition()->getRelationMappings() as $relationMapping) {
                $relationMappings[] = $relationMapping;
            }
        }

        foreach ($relationMappings as $relationMapping) {
            $mapper = $relationMapping->getRelation()->getMapper();

            $mapperId = $mapper->getMapperHash();
            if (isset($mappers[$mapperId])) {
                continue;
            }

            $mappers[$mapperId] = $mapper;

            if ($mapper instanceof self) {
                $mapper->findMappers($mappers);
            } else {
                $mappers = $mappers + $mapper->getNestedMappers();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    final public function load(LoadingContext $context, Row $row)
    {
        return $this->loadAll($context, [$row])[0];
    }

    /**
     * {@inheritDoc}
     */
    final public function loadAll(LoadingContext $context, array $rows)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'rows', $rows, Row::class);

        $loadedObjects = [];
        $newObjects    = [];
        $this->loadObjectsFromContext($context, $rows, $loadedObjects, $newObjects);
        $rowsToLoad = array_intersect_key($rows, $newObjects);

        if (!empty($rowsToLoad)) {
            $this->mapping->loadAll($context, $newObjects, $rowsToLoad);
        } else {
            $newObjects = [];
        }

        return $loadedObjects + $newObjects;
    }

    /**
     * Loads any previously loaded objects from the supplied rows and
     * constructs any required new objects.
     *
     * Note: indexes are maintained.
     *
     * @param LoadingContext $context
     * @param Row[]          $rows
     * @param ITypedObject[] $loadedObjects
     * @param ITypedObject[] $newObjects
     *
     * @return void
     */
    abstract protected function loadObjectsFromContext(LoadingContext $context, array $rows, array &$loadedObjects, array &$newObjects);

    /**
     * Constructs new objects from the supplied rows.
     *
     * Note: indexes are maintained.
     *
     * @param LoadingContext $context
     * @param Row            $row
     *
     * @return ITypedObject
     */
    final protected function constructNewObjectsFromRow(LoadingContext $context, Row $row)
    {
        return $this->mapping->constructNewObjectFromRow($row);
    }


    /**
     * Maps the entities to the rows.
     * The rows are mapped to the corresponding entity instance via the array keys.
     *
     * @param PersistenceContext $context
     * @param ITypedObject[]     $objects
     * @param Row[]              $rows
     *
     * @return void
     */
    final protected function persistObjects(PersistenceContext $context, array $objects, array $rows)
    {
        $this->mapping->persistAll($context, $objects, $rows);
    }

    /**
     * {@inheritDoc}
     */
    final public function deleteFromQuery(PersistenceContext $context, Delete $deleteQuery)
    {
        $this->mapping->delete($context, $deleteQuery);
    }
}