<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping;

use Iddigital\Cms\Core\Model\ITypedObject;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Hierarchy\EmbeddedParentObjectMapping;

/**
 * The embedded object mapper interface
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IEmbeddedObjectMapper extends IObjectMapper
{
    /**
     * @return EmbeddedParentObjectMapping
     */
    public function getMapping();

    /**
     * Gets the mapper for the object which this object is
     * embedded within.
     *
     * @return IObjectMapper|null
     */
    public function getParentMapper();

    /**
     * Gets the root entity mapper for the object which this object is
     * embedded within.
     *
     * @return IEntityMapper|null
     */
    public function getRootEntityMapper();

    /**
     * @param PersistenceContext $context
     * @param ITypedObject       $object
     * @param Row                $row
     *
     * @return void
     */
    public function persistToRow(PersistenceContext $context, ITypedObject $object, Row $row);

    /**
     * NOTE: indexes are maintained
     *
     * @param PersistenceContext $context
     * @param ITypedObject[]     $objects
     * @param Row[]              $rows
     *
     * @return void
     */
    public function persistAllToRows(PersistenceContext $context, array $objects, array $rows);

    /**
     * NOTE: indexes are maintained
     *
     * @param PersistenceContext $context
     * @param ITypedObject[]     $objects
     * @param Row[]              $rows
     *
     * @return void
     */
    public function persistAllToRowsBeforeParent(PersistenceContext $context, array $objects, array $rows);

    /**
     * NOTE: indexes are maintained
     *
     * @param PersistenceContext $context
     * @param ITypedObject[]     $objects
     * @param Row[]              $rows
     *
     * @return void
     */
    public function persistAllToRowsAfterParent(PersistenceContext $context, array $objects, array $rows);

    /**
     * @param PersistenceContext $context
     * @param Delete             $deleteQuery
     *
     * @return void
     */
    public function deleteFromQueryBeforeParent(PersistenceContext $context, Delete $deleteQuery);

    /**
     * @param PersistenceContext $context
     * @param Delete             $deleteQuery
     *
     * @return void
     */
    public function deleteFromQueryAfterParent(PersistenceContext $context, Delete $deleteQuery);

    /**
     * Returns an equivalent mapper with the columns prefixed by the supplied string.
     *
     * @param string $prefix
     *
     * @return IEmbeddedObjectMapper
     */
    public function withColumnsPrefixedBy($prefix);

    /**
     * Returns an equivalent mapper that will map objects and execute queries as if it
     * were on a separate table.
     *
     * @param Table $table
     *
     * @return IEmbeddedObjectMapper
     */
    public function asSeparateTable(Table $table);
}