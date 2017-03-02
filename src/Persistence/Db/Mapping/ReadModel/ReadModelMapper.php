<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\ReadModel;

use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Model\IEntity;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Dms\Core\Persistence\Db\Mapping\Hierarchy\ParentObjectMapping;
use Dms\Core\Persistence\Db\Mapping\Hook\IPersistHook;
use Dms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\IObjectMapper;
use Dms\Core\Persistence\Db\Mapping\ObjectMapper;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Definition\ReadMapperDefinition;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\RowSet;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\Table;

/**
 * The read model mapper class.
 *
 * This is a bit of a hack to make the read model mapper implement the
 * entity/embedded mapper interface as read models are more value objects / DTOs
 * but this is so they can be used in relations. It should not cause a
 * problem as read models are never persisted so this specific mapper
 * functionality is not used.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelMapper extends ObjectMapper implements IEntityMapper, IEmbeddedObjectMapper
{
    /**
     * @var IObjectMapper
     */
    protected $parentMapper;

    /**
     * {@inheritDoc}
     */
    public function __construct(ReadMapperDefinition $definition)
    {
        parent::__construct($definition->finalize());

        $this->parentMapper = $definition->getParentMapper();
    }

    /**
     * @param FinalizedMapperDefinition $definition
     *
     * @return ParentObjectMapping
     */
    protected function loadMapping(FinalizedMapperDefinition $definition) : \Dms\Core\Persistence\Db\Mapping\Hierarchy\ParentObjectMapping
    {
        return new ParentObjectMapping($definition);
    }

    protected function loadFromDefinition(FinalizedMapperDefinition $definition)
    {

    }

    public function onUpdatedPrimaryTable(callable $callback)
    {

    }

    /**
     * {@inheritDoc}
     */
    public function getParentMapper() : IObjectMapper
    {
        return $this->parentMapper;
    }

    /**
     * {@inheritDoc}
     */
    public function getRootEntityMapper()
    {
        if ($this->parentMapper instanceof IEmbeddedObjectMapper) {
            return $this->parentMapper->getRootEntityMapper();
        } else {
            return $this->parentMapper;
        }
    }

    /**
     * Gets the table where the primary key of the parent entity is stored.
     *
     * @return Table
     */
    public function getPrimaryTable() : Table
    {
        return $this->getRootEntityMapper()->getPrimaryTable();
    }

    /**
     * {@inheritDoc}
     */
    public function getPrimaryTableName() : string
    {
        return $this->getRootEntityMapper()->getPrimaryTableName();
    }

    /**
     * {@inheritDoc}
     */
    public function getTableWhichThisIsEmbeddedWithin() : Table
    {
        if ($this->parentMapper instanceof IEmbeddedObjectMapper) {
            return $this->parentMapper->getTableWhichThisIsEmbeddedWithin();
        } else {
            return $this->parentMapper->getDefinition()->getTable();
        }
    }

    /**
     * Gets all the tables that store the parent entity hierarchy.
     *
     * @return Table[]
     */
    public function getTables() : array
    {
        return $this->getMapping()->getMappingTables();
    }

    /**
     * @return Select
     */
    public function getSelect() : Select
    {
        $select = $this->getRawSelect();

        $this->getMapping()->addLoadToSelect($select, $select->getTableAlias());

        return $select;
    }


    /**
     * @return Select
     */
    public function getRawSelect() : Select
    {
        $select = Select::from($this->getPrimaryTable());

        $this->loadSelect($select);

        return $select;
    }

    public function loadSelect(Select $select)
    {
        foreach ($this->getDefinition()->getOrm()->getPlugins() as $plugin) {
            $plugin->loadSelect($this, $select);
        }
    }

    /**
     * @param Row[] $rows
     *
     * @return RowSet
     */
    public function rowSet(array $rows) : RowSet
    {
        return new RowSet($this->getPrimaryTable());
    }


    protected function loadObjectsFromContext(LoadingContext $context, array $rows, array &$loadedObjects, array &$newObjects)
    {
        $readModel = $this->getDefinition()->getClass()->newCleanInstance();

        foreach ($rows as $key => $row) {
            $newObjects[$key] = clone $readModel;
        }
    }

    // NOT REQUIRED

    public function persist(PersistenceContext $context, IEntity $entity) : \Dms\Core\Persistence\Db\Row
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function persistAll(PersistenceContext $context, array $entities) : array
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function delete(PersistenceContext $context, IEntity $entity)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function deleteAll(PersistenceContext $context, array $ids)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function persistToRow(PersistenceContext $context, ITypedObject $object, Row $row)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function persistAllToRows(PersistenceContext $context, array $objects, array $rows)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function persistAllToRowsBeforeParent(PersistenceContext $context, array $objects, array $rows)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function persistAllToRowsAfterParent(PersistenceContext $context, array $objects, array $rows)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function deleteFromQueryBeforeParent(PersistenceContext $context, Delete $deleteQuery)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function deleteFromQueryAfterParent(PersistenceContext $context, Delete $deleteQuery)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function withColumnsPrefixedBy(string $prefix) : IEmbeddedObjectMapper
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function isSeparateTable() : bool
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function asSeparateTable(
        string $name,
        array $extraColumns = [],
        array $extraIndexes = [],
        array $extraForeignKeys = []
    ) : IEmbeddedObjectMapper
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function addForeignKey(ForeignKey $foreignKey)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function addPersistHook(IPersistHook $persistHook)
    {
        throw NotImplementedException::method(__METHOD__);
    }
}