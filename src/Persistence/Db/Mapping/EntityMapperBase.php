<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\IEntity;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Dms\Core\Persistence\Db\Mapping\Hierarchy\ParentObjectMapping;
use Dms\Core\Persistence\Db\Mapping\Hook\IPersistHook;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\RowSet;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\Table;

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
     * @var callable[]
     */
    private $onUpdatedPrimaryTableCallbacks = [];

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
    final protected function loadMapping(FinalizedMapperDefinition $definition) : Hierarchy\ParentObjectMapping
    {
        return new ParentObjectMapping($definition);
    }

    final protected function loadFromDefinition(FinalizedMapperDefinition $definition)
    {
        $this->entityType   = $definition->getClassName();
        $this->primaryTable = $definition->getTable();
        $this->primaryKey   = $this->primaryTable->getPrimaryKeyColumnName();

        $this->tables[$this->primaryTable->getName()] = $this->primaryTable;

        foreach ($this->mapping->getMappingTables() as $table) {
            $this->tables[$table->getName()] = $table;
        }

        foreach ($this->onUpdatedPrimaryTableCallbacks as $callback) {
            $callback($this->primaryTable);
        }
    }

    /**
     * {@inheritDoc}
     */
    final public function getPrimaryTable() : \Dms\Core\Persistence\Db\Schema\Table
    {
        return $this->primaryTable;
    }

    /**
     * {@inheritDoc}
     */
    final public function getPrimaryTableName() : string
    {
        return $this->primaryTable->getName();
    }

    /**
     * @return Table[]
     */
    final public function getTables() : array
    {
        return $this->tables;
    }

    /**
     * @inheritDoc
     */
    final public function onUpdatedPrimaryTable(callable $callback)
    {
        $this->onUpdatedPrimaryTableCallbacks[] = $callback;
    }

    /**
     * @inheritDoc
     */
    final public function getSelect() : Select
    {
        $select = Select::from($this->primaryTable);

        $this->mapping->addLoadToSelect($select, $select->getTableAlias());

        return $select;
    }

    /**
     * @param Row[] $rows
     *
     * @return RowSet
     */
    final public function rowSet(array $rows) : \Dms\Core\Persistence\Db\RowSet
    {
        return new RowSet($this->primaryTable, $rows);
    }

    /**
     * @inheritDoc
     */
    final public function addForeignKey(ForeignKey $foreignKey)
    {
        $this->mapping->addForeignKey($foreignKey);
        $this->loadFromDefinition($this->getDefinition());
    }

    /**
     * @inheritDoc
     */
    final public function addPersistHook(IPersistHook $persistHook)
    {
        $this->mapping->addPersistHook($persistHook);
        $this->loadFromDefinition($this->getDefinition());
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
    final public function persist(PersistenceContext $context, IEntity $entity) : \Dms\Core\Persistence\Db\Row
    {
        return $this->persistAll($context, [$entity])[0];
    }

    /**
     * {@inheritDoc}
     */
    final public function persistAll(PersistenceContext $context, array $entities) : array
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

            $context->afterCommit(function () use (&$idEntityMap) {
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