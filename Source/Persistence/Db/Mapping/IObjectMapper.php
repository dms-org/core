<?php

namespace Dms\Core\Persistence\Db\Mapping;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\ITypedCollection;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Model\TypedCollection;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Dms\Core\Persistence\Db\Mapping\Hierarchy\ParentObjectMapping;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Row;

/**
 * The object mapper interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IObjectMapper
{
    /**
     * Initializes the mapper relations
     *
     * @return void
     */
    public function initializeRelations();

    /**
     * @return string
     */
    public function getMapperHash();

    /**
     * @return string
     */
    public function getObjectType();

    /**
     * @return FinalizedMapperDefinition
     */
    public function getDefinition();

    /**
     * @return ParentObjectMapping
     */
    public function getMapping();

    /**
     * @return IObjectMapper[]
     */
    public function getNestedMappers();

    /**
     * @param string $class
     *
     * @return IObjectMapper
     */
    public function findMapperFor($class);

    /**
     * Loads an object from the supplied row.
     *
     * @param LoadingContext $context
     * @param Row            $row
     *
     * @return ITypedObject
     * @throws InvalidArgumentException
     */
    public function load(LoadingContext $context, Row $row);

    /**
     * Loads an array of objects from the supplied rows.
     *
     * NOTE: indexes are maintained.
     *
     * @param LoadingContext $context
     * @param Row[]          $rows
     *
     * @return ITypedObject[]
     * @throws InvalidArgumentException
     */
    public function loadAll(LoadingContext $context, array $rows);

    /**
     * @param PersistenceContext $context
     * @param Delete             $deleteQuery
     *
     * @return void
     */
    public function deleteFromQuery(PersistenceContext $context, Delete $deleteQuery);

    /**
     * Builds a type-specific collection.
     *
     * @param object[] $objects
     *
     * @return ITypedCollection
     */
    public function buildCollection(array $objects);
}