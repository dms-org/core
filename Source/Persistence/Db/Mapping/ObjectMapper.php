<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Model\ITypedObject;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Hierarchy\ParentObjectMapping;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Row;

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
     * @throws \Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\IncompleteMapperDefinitionException
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
    public function initializeRelations()
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

    final protected function findMappers(array &$mappers)
    {
        if (!$this->getDefinition()) {
            throw new InvalidOperationException('Mapper has not been defined yet');
        }

        $relations = $this->getDefinition()->getRelations();

        foreach ($this->getDefinition()->getSubClassMappings() as $mapping) {
            foreach ($mapping->getDefinition()->getRelations() as $relation) {
                $relations[] = $relation;
            }
        }

        foreach ($relations as $relation) {
            $mapper = $relation->getMapper();

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
     * {@inheritDoc}
     */
    final public function loadAllAsArray(LoadingContext $context, array $rows)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'rows', $rows, Row::class);

        return $this->mapping->loadAllRaw($context, $rows);
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