<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Hierarchy\ParentObjectMapping;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Persistence\Db\RowSet;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The entity mapper base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class EntityMapperBase extends ObjectMapper implements IEntityMapper
{
    /**
     * @var string
     */
    private $entityType;

    /**
     * @var Table
     */
    private $primaryTable;

    /**
     * @var Table[]
     */
    private $tables = [];

    /**
     * @var string
     */
    private $primaryKey;

    /**
     * EntityMapperBase constructor.
     *
     * @param FinalizedMapperDefinition $definition
     */
    public function __construct(FinalizedMapperDefinition $definition)
    {
        parent::__construct($definition);
    }

    /**
     * {@inheritDoc}
     */
    final protected function loadMapping(FinalizedMapperDefinition $definition)
    {
        return new ParentObjectMapping($definition);
    }

    final protected function loadFromDefinition(FinalizedMapperDefinition $definition)
    {
        $this->entityType       = $definition->getClassName();
        $this->primaryTable     = $definition->getTable();
        $this->primaryKey       = $this->primaryTable->getPrimaryKeyColumnName();

        $this->tables[$this->primaryTable->getName()] = $this->primaryTable;

        foreach ($this->mapping->getMappingTables() as $table) {
            $this->tables[$table->getName()] = $table;
        }
    }

    /**
     * {@inheritDoc}
     */
    final public function getPrimaryTable()
    {
        return $this->primaryTable;
    }

    /**
     * {@inheritDoc}
     */
    final public function getPrimaryTableName()
    {
        return $this->primaryTable->getName();
    }

    /**
     * @return Table[]
     */
    final public function getTables()
    {
        return $this->tables;
    }

    /**
     * @inheritDoc
     */
    final public function getSelect()
    {
        $select = Select::from($this->primaryTable);

        $this->mapping->addLoadToSelect($select);

        return $select;
    }

    /**
     * @param Row[] $rows
     *
     * @return RowSet
     */
    final public function rowSet(array $rows)
    {
        return new RowSet($this->primaryTable, $rows);
    }

    /**
     * @inheritDoc
     */
    final public function addForeignKey(ForeignKey $foreignKey)
    {
        $this->primaryTable                           = $this->primaryTable->withForeignKeys(
                array_merge($this->primaryTable->getForeignKeys(), [$foreignKey])
        );
        $this->mapping                                = $this->mapping->withPrimaryTable($this->primaryTable);
        $this->tables[$this->primaryTable->getName()] = $this->primaryTable;
    }

    /**
     * @inheritDoc
     */
    final protected function loadObjectsFromContext(LoadingContext $context, array $rows, array &$loadedObjects, array &$newObjects)
    {
        $identityMap = $context->getIdentityMap($this->entityType);

        /** @var Row[] $rows */
        foreach ($rows as $key => $row) {
            $primaryKey = $row->getColumn($this->primaryKey);

            if ($identityMap->has($primaryKey)) {
                $loadedObjects[$key] = $identityMap->get($primaryKey);
            } else {
                /** @var IEntity $entity */
                $entity = $this->constructNewObjectsFromRow($context, $row);
                $entity->hydrate(['id' => $primaryKey]);
                $identityMap->add($entity);
                $newObjects[$key] = $entity;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    final public function persist(PersistenceContext $context, IEntity $entity)
    {
        return $this->persistAll($context, [$entity])[0];
    }

    /**
     * {@inheritDoc}
     */
    final public function persistAll(PersistenceContext $context, array $entities)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'entities', $entities, $this->entityType);

        $rows          = [];
        $persistedRows = [];
        /** @var IEntity[] $idEntityMap */
        $idEntityMap = [];

        /** @var IEntity[] $entities */
        foreach ($entities as $key => $entity) {
            if ($context->isPersisted($entity)) {
                unset($entities[$key]);
                $persistedRows[$key] = $context->getPersistedRowFor($entity);
            } else {
                $row = new Row($this->primaryTable);

                if ($entity->hasId()) {
                    $row->setPrimaryKey($entity->getId());
                } else {
                    $row->onInsertPrimaryKey(function ($id) use (&$idEntityMap, $entity) {
                        $idEntityMap[$id] = $entity;
                    });
                }

                $rows[$key] = $row;
                $context->markPersisted($entity, $row);
            }
        }

        if (!empty($entities)) {
            $this->persistObjects($context, $entities, $rows);

            $context->onCompletion(function () use (&$idEntityMap) {
                foreach ($idEntityMap as $id => $entity) {
                    $entity->setId($id);
                }
            });
        }

        return $rows + $persistedRows;
    }

    /**
     * {@inheritDoc}
     */
    final public function delete(PersistenceContext $context, IEntity $entity)
    {
        $this->deleteAll($context, [$entity]);
    }

    /**
     * {@inheritDoc}
     */
    final public function deleteAll(PersistenceContext $context, array $ids)
    {
        $idParams = [];
        /** @var IEntity[] $entities */
        foreach ($ids as $id) {
            $idParams[] = Expr::idParam($id);
        }

        $primaryKey  = Expr::column($this->getPrimaryTableName(), $this->getPrimaryTable()->getPrimaryKeyColumn());
        $deleteQuery = Delete::from($this->getPrimaryTable())->where(Expr::in($primaryKey, Expr::tuple($idParams)));

        $this->deleteFromQuery($context, $deleteQuery);
    }
}