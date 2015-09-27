<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\ITypedObject;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Hierarchy\ParentObjectMapping;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Row;

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
     * Loads an array of arrays from the supplied rows.
     *
     * NOTE: indexes are maintained.
     *
     * @param LoadingContext $context
     * @param Row[]          $rows
     *
     * @return ITypedObject[]
     * @throws InvalidArgumentException
     */
    public function loadAllAsArray(LoadingContext $context, array $rows);

    /**
     * @param PersistenceContext $context
     * @param Delete             $deleteQuery
     *
     * @return void
     */
    public function deleteFromQuery(PersistenceContext $context, Delete $deleteQuery);
}