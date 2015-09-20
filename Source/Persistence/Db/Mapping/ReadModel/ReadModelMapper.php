<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\NotImplementedException;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\ITypedObject;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Hierarchy\ParentObjectMapping;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\Definition\ReadMapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Persistence\Db\RowSet;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

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
     * @var IEntityMapper
     */
    protected $parentMapper;

    /**
     * {@inheritDoc}
     */
    public function __construct(ReadMapperDefinition $definition)
    {
        parent::__construct($definition->finalize());

        $parentMapper = $definition->getParentMapper();

        if (!($parentMapper instanceof IEntityMapper)) {
            throw InvalidArgumentException::format(
                    'Invalid definition given to read model mapper: parent mapper must be instance of %s, %s given',
                    IEntityMapper::class, get_class($this->parentMapper)
            );
        }

        $this->parentMapper = $parentMapper;
    }

    /**
     * @param FinalizedMapperDefinition $definition
     *
     * @return ParentObjectMapping
     */
    protected function loadMapping(FinalizedMapperDefinition $definition)
    {
        return new ParentObjectMapping($definition);
    }

    protected function loadFromDefinition(FinalizedMapperDefinition $definition)
    {

    }

    /**
     * {@inheritDoc}
     */
    public function getParentMapper()
    {
        return $this->parentMapper;
    }

    /**
     * {@inheritDoc}
     */
    public function getRootEntityMapper()
    {
        return $this->parentMapper;
    }

    /**
     * Gets the table where the primary key of the parent entity is stored.
     *
     * @return Table
     */
    public function getPrimaryTable()
    {
        return $this->parentMapper->getPrimaryTable();
    }

    /**
     * @inheritDoc
     */
    public function getPrimaryTableName()
    {
        return $this->parentMapper->getPrimaryTableName();
    }

    /**
     * Gets all the tables that store the parent entity hierarchy.
     *
     * @return Table[]
     */
    public function getTables()
    {
        return $this->getMapping()->getMappingTables();
    }

    /**
     * @return Select
     */
    public function getSelect()
    {
        $select = Select::from($this->getPrimaryTable());
        $this->getMapping()->addLoadToSelect($select);

        return $select;
    }

    /**
     * @param Row[] $rows
     *
     * @return RowSet
     */
    public function rowSet(array $rows)
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

    public function persist(PersistenceContext $context, IEntity $entity)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function persistAll(PersistenceContext $context, array $entities)
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

    public function withColumnsPrefixedBy($prefix)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function asSeparateTable(Table $table)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function addForeignKey(ForeignKey $foreignKey)
    {
        throw NotImplementedException::method(__METHOD__);
    }

}