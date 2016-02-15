<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping;

use Dms\Core\Model\IEntity;
use Dms\Core\Model\ITypedCollection;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\Hook\IPersistHook;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\Schema\ForeignKey;

/**
 * The null mapper class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NullObjectMapper implements IEntityMapper
{

    public function initializeRelations()
    {
    }

    public function onUpdatedPrimaryTable(callable $callback)
    {
    }


    public function getMapperHash() : string
    {
    }


    public function getObjectType() : string
    {
    }


    public function getDefinition() : Definition\FinalizedMapperDefinition
    {
    }


    public function getMapping() : Hierarchy\ParentObjectMapping
    {
    }


    public function getNestedMappers() : array
    {
    }

    public function findMapperFor(string $class) : IObjectMapper
    {
    }

    public function load(LoadingContext $context, Row $row) : \Dms\Core\Model\ITypedObject
    {
    }


    public function loadAll(LoadingContext $context, array $rows) : array
    {
    }


    public function deleteFromQuery(PersistenceContext $context, Delete $deleteQuery)
    {
    }

    public function getPrimaryTable() : \Dms\Core\Persistence\Db\Schema\Table
    {
    }

    public function getPrimaryTableName() : string
    {
    }

    public function getTables() : array
    {
    }

    public function getSelect() : \Dms\Core\Persistence\Db\Query\Select
    {
    }

    public function addForeignKey(ForeignKey $foreignKey)
    {
    }

    public function addPersistHook(IPersistHook $persistHook)
    {
    }

    public function rowSet(array $rows) : \Dms\Core\Persistence\Db\RowSet
    {
    }

    public function persist(PersistenceContext $context, IEntity $entity) : \Dms\Core\Persistence\Db\Row
    {
    }

    public function persistAll(PersistenceContext $context, array $entities) : array
    {
    }

    public function delete(PersistenceContext $context, IEntity $entity)
    {
    }

    public function deleteAll(PersistenceContext $context, array $ids)
    {

    }

    public function buildCollection(array $objects) : \Dms\Core\Model\ITypedCollection
    {

    }
}