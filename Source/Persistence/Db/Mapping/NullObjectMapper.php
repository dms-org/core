<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping;

use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;

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


    public function loadAllAsArray(LoadingContext $context, array $rows)
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
}