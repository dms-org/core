<?php

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


    public function getMapperHash()
    {
    }


    public function getObjectType()
    {
    }


    public function getDefinition()
    {
    }


    public function getMapping()
    {
    }


    public function getNestedMappers()
    {
    }

    public function findMapperFor($class)
    {
    }

    public function load(LoadingContext $context, Row $row)
    {
    }


    public function loadAll(LoadingContext $context, array $rows)
    {
    }


    public function deleteFromQuery(PersistenceContext $context, Delete $deleteQuery)
    {
    }

    public function getPrimaryTable()
    {
    }

    public function getPrimaryTableName()
    {
    }

    public function getTables()
    {
    }

    public function getSelect()
    {
    }

    public function addForeignKey(ForeignKey $foreignKey)
    {
    }

    public function addPersistHook(IPersistHook $persistHook)
    {
    }

    public function rowSet(array $rows)
    {
    }

    public function persist(PersistenceContext $context, IEntity $entity)
    {
    }

    public function persistAll(PersistenceContext $context, array $entities)
    {
    }

    public function delete(PersistenceContext $context, IEntity $entity)
    {
    }

    public function deleteAll(PersistenceContext $context, array $ids)
    {

    }

    public function buildCollection(array $objects)
    {

    }
}