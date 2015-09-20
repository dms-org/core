<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Persistence\Db\RowSet;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The null mapper class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NullObjectMapper implements IEntityMapper
{
    /**
     * @inheritDoc
     */
    public function initializeRelations()
    {
    }

    /**
     * @inheritDoc
     */
    public function getMapperHash()
    {
    }

    /**
     * @inheritDoc
     */
    public function getObjectType()
    {
    }

    /**
     * @inheritDoc
     */
    public function getDefinition()
    {
    }

    /**
     * @inheritDoc
     */
    public function getMapping()
    {
    }

    /**
     * @inheritDoc
     */
    public function getNestedMappers()
    {
    }

    /**
     * @inheritDoc
     */
    public function load(LoadingContext $context, Row $row)
    {
    }

    /**
     * @inheritDoc
     */
    public function loadAll(LoadingContext $context, array $rows)
    {
    }

    /**
     * @inheritDoc
     */
    public function loadAllAsArray(LoadingContext $context, array $rows)
    {
    }

    /**
     * @inheritDoc
     */
    public function deleteFromQuery(PersistenceContext $context, Delete $deleteQuery)
    {
    }

    /**
     * Gets the table where the primary key of the parent entity is stored.
     *
     * @return Table
     */
    public function getPrimaryTable()
    {
        // TODO: Implement getPrimaryTable() method.
    }

    /**
     * Gets the table name where the primary key of the parent entity is stored.
     *
     * @return string
     */
    public function getPrimaryTableName()
    {
        // TODO: Implement getPrimaryTableName() method.
    }

    /**
     * Gets all the tables that store this entity hierarchy.
     *
     * @return Table[]
     */
    public function getTables()
    {
        // TODO: Implement getTables() method.
    }

    /**
     * @return Select
     */
    public function getSelect()
    {
        // TODO: Implement getSelect() method.
    }

    /**
     * Adds a foreign key to the primary table of the entity mapper.
     *
     * @param ForeignKey $foreignKey
     *
     * @return void
     */
    public function addForeignKey(ForeignKey $foreignKey)
    {
        // TODO: Implement addForeignKey() method.
    }

    /**
     * @param Row[] $rows
     *
     * @return RowSet
     */
    public function rowSet(array $rows)
    {
        // TODO: Implement rowSet() method.
    }

    /**
     * @param PersistenceContext $context
     * @param IEntity            $entity
     *
     * @return Row
     * @throws InvalidArgumentException
     */
    public function persist(PersistenceContext $context, IEntity $entity)
    {
        // TODO: Implement persist() method.
    }

    /**
     * NOTE: indexes are maintained
     *
     * @param PersistenceContext $context
     * @param IEntity[]          $entities
     *
     * @return Row[]
     * @throws InvalidArgumentException
     */
    public function persistAll(PersistenceContext $context, array $entities)
    {
        // TODO: Implement persistAll() method.
    }

    /**
     * @param PersistenceContext $context
     * @param IEntity            $entity
     *
     * @return void
     */
    public function delete(PersistenceContext $context, IEntity $entity)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param PersistenceContext $context
     * @param int[]              $ids
     *
     * @return void
     */
    public function deleteAll(PersistenceContext $context, array $ids)
    {
        // TODO: Implement deleteAll() method.
    }
}