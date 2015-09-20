<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Orm\OrmDefinition;
use Iddigital\Cms\Core\Persistence\Db\Schema\Database;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The orm base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Orm implements IOrm
{
    /**
     * @var IEntityMapper[]
     */
    private $entityMappers = [];

    /**
     * @var callable[]
     */
    private $embeddedObjectMapperFactories = [];

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
     */
    public function __construct()
    {
        $definition = new OrmDefinition();
        $this->define($definition);

        $definition->finalize(function (array $entityMapperFactories, array $embeddedObjectMapperFactories, array $includedOrms) {
            $this->includedOrms = $includedOrms;
            $this->initializeMappers($entityMapperFactories, $embeddedObjectMapperFactories);
        });
    }

    private function initializeMappers(array $entityMapperFactories, array $embeddedObjectMapperFactories)
    {
        foreach ($embeddedObjectMapperFactories as $valueObjectType => $factory) {
            $this->embeddedObjectMapperFactories[$valueObjectType] = $factory;
        }

        foreach ($entityMapperFactories as $factory) {
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

        // TODO: add checks for duplicate tables
        $uniqueTables = [];
        foreach ($tables as $table) {
            $uniqueTables[$table->getName()] = $table;
        }

        $this->database = new Database($uniqueTables);
    }

    private function loadMapperTables(IEntityMapper $mapper)
    {
        $tables = [];

        foreach ($mapper->getTables() as $table) {
            $tables[] = $table;
        }

        foreach ($mapper->getNestedMappers() as $innerMapper) {
            if ($innerMapper instanceof IEntityMapper) {
                foreach ($innerMapper->getTables() as $table) {
                    $tables[] = $table;
                }
            }
        }

        foreach ($mapper->getDefinition()->getRelations() as $relation) {
            foreach ($relation->getRelationshipTables() as $table) {
                $tables[] = $table;
            }
        }

        return $tables;
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
    final public function getEntityMappers()
    {
        return $this->entityMappers;
    }

    /**
     * @inheritDoc
     */
    final  public function hasEntityMapper($entityClass, $tableName = null)
    {
        return $this->findEntityMapper($entityClass, $tableName) !== null;
    }

    /**
     * @inheritDoc
     */
    final public function getEntityMapper($entityClass, $tableName = null)
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

    final public function findEntityMapper($entityClass, $tableName = null)
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
    final public function getEmbeddedObjectTypes()
    {
        return array_keys($this->embeddedObjectMapperFactories);
    }

    /**
     * @inheritDoc
     */
    final public function hasEmbeddedObjectMapper($valueObjectClass)
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
    final public function loadEmbeddedObjectMapper(IObjectMapper $parentMapper, $valueObjectClass)
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
    final public function getIncludedOrms()
    {
        return $this->includedOrms;
    }

    /**
     * @inheritDoc
     */
    final  public function getDatabase()
    {
        if (!$this->database) {
            $this->initializeDb();
        }

        return $this->database;
    }
}