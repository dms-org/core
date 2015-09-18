<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Persistence\Db\RowSet;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;


/**
 * The entity mapper interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IEntityMapper extends IObjectMapper
{
    /**
     * Gets the table where the primary key of the parent entity is stored.
     *
     * @return Table
     */
    public function getPrimaryTable();

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