<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping;

use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Model\ITypedCollection;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Model\Type\IType;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Dms\Core\Persistence\Db\Mapping\Hierarchy\ParentObjectMapping;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Row;

/**
 * This class wraps an object mapper.
 *
 * It can only be used for reading.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadOnlyObjectMapperProxy implements IObjectMapper
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
    public function getDefinition() : FinalizedMapperDefinition
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
    public function findMapperFor(string $class) : IObjectMapper
    {
        return $this->mapper->findMapperFor($class);
    }

    /**
     * {@inheritDoc}
     */
    public function getMapping() : ParentObjectMapping
    {
        return $this->mapper->getMapping();
    }

    /**
     * @inheritDoc
     */
    public function load(LoadingContext $context, Row $row) : ITypedObject
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
}