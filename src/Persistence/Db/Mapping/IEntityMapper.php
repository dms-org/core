<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\IEntity;
use Dms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Dms\Core\Persistence\Db\Mapping\Hook\IPersistHook;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\RowSet;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\Table;


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
    public function getPrimaryTable() : \Dms\Core\Persistence\Db\Schema\Table;

    /**
     * Gets the table name where the primary key of the parent entity is stored.
     *
     * @return string
     */
    public function getPrimaryTableName() : string;

    /**
     * Gets all the tables that store this entity hierarchy.
     *
     * @return Table[]
     */
    public function getTables() : array;

    /**
     * @return FinalizedMapperDefinition
     */
    public function getDefinition() : Definition\FinalizedMapperDefinition;

    /**
     * @return Select
     */
    public function getSelect() : Select;

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
    public function rowSet(array $rows) : \Dms\Core\Persistence\Db\RowSet;

    /**
     * @param PersistenceContext $context
     * @param IEntity            $entity
     *
     * @return Row
     * @throws InvalidArgumentException
     */
    public function persist(PersistenceContext $context, IEntity $entity) : \Dms\Core\Persistence\Db\Row;

    /**
     * NOTE: indexes are maintained
     *
     * @param PersistenceContext $context
     * @param IEntity[]          $entities
     *
     * @return Row[]
     * @throws InvalidArgumentException
     */
    public function persistAll(PersistenceContext $context, array $entities) : array;

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