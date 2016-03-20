<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Ioc\IIocContainer;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Mapping\Definition\Orm\OrmDefinition;
use Dms\Core\Persistence\Db\Schema\Database;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Util\Debug;

/**
 * The orm base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Orm implements IOrm
{
    /**
     * @var string
     */
    private $namespace = '';

    /**
     * @var callable[]
     */
    private $embeddedObjectMapperFactories;

    /**
     * @var callable[]
     */
    private $entityMapperFactories;

    /**
     * @var IEntityMapper[]
     */
    private $entityMappers = [];

    /**
     * @var IOrm[]
     */
    private $includedOrms = [];

    /**
     * @var Database|null
     */
    private $database;

    /**
     * Orm constructor.
     *
     * @param IIocContainer $iocContainer
     */
    public function __construct(IIocContainer $iocContainer = null)
    {
        $definition = new OrmDefinition($iocContainer);
        $this->define($definition);

        $definition->finalize(function (array $entityMapperFactories, array $embeddedObjectMapperFactories, array $includedOrms) {
            $this->includedOrms = $includedOrms;
            $this->initializeMappers($entityMapperFactories, $embeddedObjectMapperFactories);
        });
    }

    private function initializeMappers(array $entityMapperFactories, array $embeddedObjectMapperFactories)
    {
        $this->entityMapperFactories         = $entityMapperFactories;
        $this->embeddedObjectMapperFactories = $embeddedObjectMapperFactories;

        $this->initializeEntityMappers();
    }

    /**
     * @return void
     */
    private function initializeEntityMappers()
    {
        $this->entityMappers = [];
        $this->database      = null;

        foreach ($this->entityMapperFactories as $factory) {
            /** @var IEntityMapper $mapper */
            $mapper                = $factory($this);
            $this->entityMappers[] = $mapper;
        }

        foreach ($this->entityMappers as $mapper) {
            $mapper->initializeRelations();

            foreach ($mapper->getNestedMappers() as $nestedMapper) {
                $nestedMapper->initializeRelations();
            }
        }
    }

    private function initializeDb()
    {
        /** @var Table[] $tables */
        $tables = [];

        foreach ($this->entityMappers as $mapper) {
            foreach ($this->loadMapperTables($mapper) as $table) {
                $tables[] = $table;
            }
        }

        foreach ($this->includedOrms as $orm) {
            foreach ($orm->getDatabase()->getTables() as $table) {
                $tables[] = $table;
            }
        }

        $uniqueTables = [];
        foreach ($tables as $table) {
            $uniqueTables[spl_object_hash($table)] = $table;
        }

        $this->database = new Database($uniqueTables);
    }

    /**
     * @return string
     */
    public function getNamespace() : string
    {
        return $this->namespace;
    }

    /**
     * @inheritDoc
     */
    public function inNamespace(string $prefix) : IOrm
    {
        if ($prefix === '') {
            return $this;
        }

        $clone = clone $this;

        foreach ($clone->includedOrms as $key => $orm) {
            $clone->includedOrms[$key] = $orm->inNamespace($prefix);
        }

        $clone->namespace = $prefix . $this->namespace;

        $clone->initializeEntityMappers();

        return $clone;
    }

    private function loadMapperTables(IObjectMapper $mapper)
    {
        $tables = [];

        if ($mapper instanceof IEntityMapper) {
            foreach ($mapper->getTables() as $table) {
                $tables[] = $table;
            }
        }

        foreach ($mapper->getNestedMappers() as $innerMapper) {
            if ($innerMapper instanceof IEntityMapper) {
                foreach ($innerMapper->getTables() as $table) {
                    $tables[] = $table;
                }
            }

            $this->loadTablesFromMapperRelations($tables, $innerMapper);
        }

        $this->loadTablesFromMapperRelations($tables, $mapper);

        return $tables;
    }

    /**
     * @param Table[]       $tables
     * @param IObjectMapper $mapper
     */
    private function loadTablesFromMapperRelations(array &$tables, IObjectMapper $mapper)
    {
        foreach ($mapper->getDefinition()->getRelationMappings() as $relationMapping) {
            foreach ($relationMapping->getRelation()->getRelationshipTables() as $table) {
                $tables[] = $table;
            }
        }
    }

    /**
     * Defines the object mappers registered in the orm.
     *
     * @param OrmDefinition $orm
     *
     * @return void
     */
    abstract protected function define(OrmDefinition $orm);

    /**
     * @inheritDoc
     */
    final public function getEntityMappers() : array
    {
        return $this->entityMappers;
    }

    /**
     * @inheritDoc
     */
    final  public function hasEntityMapper(string $entityClass, string $tableName = null) : bool
    {
        return $this->findEntityMapper($entityClass, $tableName) !== null;
    }

    /**
     * @inheritDoc
     */
    final public function getEntityMapper(string $entityClass, string $tableName = null) : IEntityMapper
    {
        $mapper = $this->findEntityMapper($entityClass, $tableName);

        if (!$mapper) {
            throw InvalidArgumentException::format(
                    'Could not find entity mapper for %s %s',
                    $entityClass, $tableName ? 'on table ' . $tableName : ''
            );
        }

        return $mapper;
    }

    final public function findEntityMapper(string $entityClass, string $tableName = null)
    {
        $mappers = [];

        foreach ($this->entityMappers as $mapper) {
            if ($mapper->getObjectType() === $entityClass) {
                $mappers[$mapper->getPrimaryTableName()] = $mapper;
            }
        }

        if ($tableName === null) {
            if (count($mappers) > 1) {
                throw InvalidArgumentException::format(
                        'Ambiguous entity mapper reference, %s is mapped to multiple tables: table must be one of (%s), none given',
                        $entityClass, Debug::formatValues(array_keys($mappers))
                );
            } elseif ($mappers) {
                return reset($mappers) ?: null;
            }
        } elseif (isset($mappers[$tableName])) {
            return $mappers[$tableName];
        }

        foreach ($this->includedOrms as $orm) {
            if ($mapper = $orm->findEntityMapper($entityClass, $tableName)) {
                return $mapper;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    final public function getEmbeddedObjectTypes() : array
    {
        return array_keys($this->embeddedObjectMapperFactories);
    }

    /**
     * @inheritDoc
     */
    final public function hasEmbeddedObjectMapper(string $valueObjectClass) : bool
    {
        if ($this->findEmbeddedObjectMapperFactory($valueObjectClass) !== null) {
            return true;
        }

        foreach ($this->includedOrms as $orm) {
            if ($orm->hasEmbeddedObjectMapper($valueObjectClass)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    final public function loadEmbeddedObjectMapper(IObjectMapper $parentMapper, string $valueObjectClass) : IEmbeddedObjectMapper
    {
        $factory = $this->findEmbeddedObjectMapperFactory($valueObjectClass);

        if ($factory) {
            return $factory($this, $parentMapper);
        }

        foreach ($this->includedOrms as $orm) {
            if ($orm->hasEmbeddedObjectMapper($valueObjectClass)) {
                return $orm->loadEmbeddedObjectMapper($parentMapper, $valueObjectClass);
            }
        }

        throw InvalidArgumentException::format(
                'Could not find embedded object mapper for %s',
                $valueObjectClass
        );
    }

    final protected function findEmbeddedObjectMapperFactory($valueObjectClass)
    {
        if (isset($this->embeddedObjectMapperFactories[$valueObjectClass])) {
            return $this->embeddedObjectMapperFactories[$valueObjectClass];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    final public function getIncludedOrms() : array
    {
        return $this->includedOrms;
    }

    /**
     * @inheritDoc
     */
    final public function getDatabase() : \Dms\Core\Persistence\Db\Schema\Database
    {
        if (!$this->database) {
            $this->initializeDb();
        }

        return $this->database;
    }

    /**
     * @inheritDoc
     */
    public function loadRelatedEntityType(string $entityType, string $idPropertyName) : string
    {
        $mapper = $this->getEntityMapper($entityType);

        $propertyRelationMap = $mapper->getDefinition()->getPropertyRelationMap();

        if (!isset($propertyRelationMap[$idPropertyName])) {
            throw InvalidArgumentException::format(
                    'Could not load related entity type for property %s::$%s: '
                    . 'the property must mapped to a relation, expecting one of (%s), \'%s\' given',
                    $entityType, $idPropertyName, Debug::formatValues(array_keys($propertyRelationMap)), $idPropertyName
            );
        }

        return $propertyRelationMap[$idPropertyName]->getMapper()->getObjectType();
    }

    /**
     * @inheritDoc
     */
    public function getEntityDataSourceProvider(IConnection $connection) : \Dms\Core\Model\Criteria\IEntitySetProvider
    {
        return new EntityRepositoryProvider($this, $connection);
    }
}