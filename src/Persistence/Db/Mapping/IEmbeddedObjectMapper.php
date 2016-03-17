<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping;

use Dms\Core\Model\ITypedObject;
use Dms\Core\Persistence\Db\Mapping\Hierarchy\EmbeddedParentObjectMapping;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\Index;
use Dms\Core\Persistence\Db\Schema\Table;

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
    public function getMapping() : Hierarchy\ParentObjectMapping;

    /**
     * Gets the mapper for the object which this object is
     * embedded within.
     *
     * @return IObjectMapper
     */
    public function getParentMapper() : IObjectMapper;

    /**
     * Gets the root entity mapper for the object which this object is
     * embedded within.
     *
     * @return IEntityMapper|null
     */
    public function getRootEntityMapper();

    /**
     * Gets the table which this mapper maps to.
     *
     * @return Table
     */
    public function getTableWhichThisIsEmbeddedWithin() : Table;

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
    public function withColumnsPrefixedBy(string $prefix) : IEmbeddedObjectMapper;

    /**
     * Returns whether this mapper is mapping to a table of its own or is embedded
     * in the parent entities.
     *
     * @return bool
     */
    public function isSeparateTable() : bool;

    /**
     * Returns an equivalent mapper that will map objects and execute queries as if it
     * were on a separate table.
     *
     * @param string       $name
     * @param Column[]     $extraColumns
     * @param Index[]      $extraIndexes
     * @param ForeignKey[] $extraForeignKeys
     *
     * @return IEmbeddedObjectMapper
     */
    public function asSeparateTable(string $name, array $extraColumns = [], array $extraIndexes = [], array $extraForeignKeys = []) : IEmbeddedObjectMapper;
}