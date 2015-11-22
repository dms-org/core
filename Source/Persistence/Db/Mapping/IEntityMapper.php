<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Hook\IPersistHook;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Persistence\Db\RowSet;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;


/**
 * The entity mapper interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IEntityMapper extends IObjectMapper
{
    /**
     * Initializes the entity mapper relations
     *
     * @return void
     */
    public function initializeRelations();

    /**
     * Defines a callback for when the primary table is updated.
     *
     * @param callable $callback
     *
     * @return void
     */
    public function onUpdatedPrimaryTable(callable $callback);

    /**
     * Gets the table where the primary key of the parent entity is stored.
     *
     * @return Table
     */
    public function getPrimaryTable();

    /**
     * Gets the table name where the primary key of the parent entity is stored.
     *
     * @return string
     */
    public function getPrimaryTableName();

    /**
     * Gets all the tables that store this entity hierarchy.
     *
     * @return Table[]
     */
    public function getTables();

    /**
     * @return FinalizedMapperDefinition
     */
    public function getDefinition();

    /**
     * @return Select
     */
    public function getSelect();

    /**
     * Adds a foreign key to the primary table of the entity mapper.
     *
     * @param ForeignKey $foreignKey
     *
     * @return void
     */
    public function addForeignKey(ForeignKey $foreignKey);

    /**
     * Adds a persist hook to the entity mapper.
     *
     * @param IPersistHook $persistHook
     *
     * @return void
     */
    public function addPersistHook(IPersistHook $persistHook);

    /**
     * @param Row[] $rows
     *
     * @return RowSet
     */
    public function rowSet(array $rows);

    /**
     * @param PersistenceContext $context
     * @param IEntity            $entity
     *
     * @return Row
     * @throws InvalidArgumentException
     */
    public function persist(PersistenceContext $context, IEntity $entity);

    /**
     * NOTE: indexes are maintained
     *
     * @param PersistenceContext $context
     * @param IEntity[]          $entities
     *
     * @return Row[]
     * @throws InvalidArgumentException
     */
    public function persistAll(PersistenceContext $context, array $entities);

    /**
     * @param PersistenceContext $context
     * @param IEntity            $entity
     *
     * @return void
     */
    public function delete(PersistenceContext $context, IEntity $entity);

    /**
     * @param PersistenceContext $context
     * @param int[]              $ids
     *
     * @return void
     */
    public function deleteAll(PersistenceContext $context, array $ids);
}