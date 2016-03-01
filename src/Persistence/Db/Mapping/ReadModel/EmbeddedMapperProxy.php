<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\ReadModel;

use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Model\IEntity;
use Dms\Core\Model\ITypedCollection;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Model\Type\IType;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;
use Dms\Core\Persistence\Db\Mapping\IObjectMapper;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Row;

/**
 * This class wraps an entity mapper as an embedded mapper.
 *
 * It can only be used for reading.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedMapperProxy implements IEmbeddedObjectMapper
{
    /**
     * @var IObjectMapper
     */
    protected $mapper;

    /**
     * {@inheritDoc}
     */
    public function __construct(IObjectMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @inheritDoc
     */
    public function initializeRelations()
    {
        $this->mapper->initializeRelations();
    }

    /**
     * @inheritDoc
     */
    public function getMapperHash() : string
    {
        return $this->mapper->getMapperHash();
    }

    /**
     * @inheritDoc
     */
    public function getObjectType() : string
    {
        return $this->mapper->getObjectType();
    }

    /**
     * @inheritDoc
     */
    public function getDefinition() : \Dms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition
    {
        return $this->mapper->getDefinition();
    }

    /**
     * @inheritDoc
     */
    public function getNestedMappers() : array
    {
        return $this->mapper->getNestedMappers();
    }

    /**
     * @inheritDoc
     */
    public function findMapperFor(string $class) : \Dms\Core\Persistence\Db\Mapping\IObjectMapper
    {
        return $this->mapper->findMapperFor($class);
    }

    /**
     * {@inheritDoc}
     */
    public function getMapping() : \Dms\Core\Persistence\Db\Mapping\Hierarchy\ParentObjectMapping
    {
        return $this->mapper->getMapping();
    }

    /**
     * {@inheritDoc}
     */
    public function getParentMapper() : \Dms\Core\Persistence\Db\Mapping\IObjectMapper
    {
        return $this->mapper;
    }

    /**
     * {@inheritDoc}
     */
    public function getRootEntityMapper()
    {
        return $this->mapper;
    }

    /**
     * @inheritDoc
     */
    public function load(LoadingContext $context, Row $row) : \Dms\Core\Model\ITypedObject
    {
        return $this->mapper->load($context, $row);
    }

    /**
     * @inheritDoc
     */
    public function loadAll(LoadingContext $context, array $rows) : array
    {
        return $this->mapper->loadAll($context, $rows);
    }

    /**
     * @inheritDoc
     */
    public function buildCollection(array $objects) : ITypedCollection
    {
        return $this->mapper->buildCollection($objects);
    }

    /**
     * @inheritDoc
     */
    public function getCollectionType() : IType
    {
        return $this->mapper->getCollectionType();
    }

    // NOT REQUIRED

    public function deleteFromQuery(PersistenceContext $context, Delete $deleteQuery)
    {
        throw NotImplementedException::method(__METHOD__);
    }

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

    public function withColumnsPrefixedBy(string $prefix) : \Dms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function asSeparateTable(string $name, array $extraColumns = [], array $extraIndexes = [], array $extraForeignKeys = []) : \Dms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper
    {
        throw NotImplementedException::method(__METHOD__);
    }
}