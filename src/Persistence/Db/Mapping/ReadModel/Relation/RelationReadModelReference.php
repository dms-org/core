<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\ReadModel\Relation;

use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Model\ITypedCollection;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;
use Dms\Core\Model\TypedCollection;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\ReadModel\ReadModelMapper;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\IToManyRelationReference;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\IToOneRelationReference;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\RelationObjectReference;
use Dms\Core\Persistence\Db\PersistenceContext;

/**
 * The relation read model reference base class.
 *
 * This is a read-only implementation used for constructing read models.
 * Persistence methods are not implemented.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RelationReadModelReference extends RelationObjectReference implements IToOneRelationReference, IToManyRelationReference
{
    /**
     * @param ReadModelMapper $mapper
     */
    public function __construct(ReadModelMapper $mapper)
    {
        parent::__construct($mapper);
    }

    /**
     * @inheritDoc
     */
    public function getCollectionType() : IType
    {
        return $this->mapper->getCollectionType();
    }

    /**
     * @inheritDoc
     */
    public function getValueType() : IType
    {
        return Type::object($this->mapper->getObjectType());
    }

    /**
     * {@inheritDoc}
     */
    public function loadValues(LoadingContext $context, array $rows) : array
    {
        return $this->mapper->loadAll($context, $rows);
    }

    /**
     * {@inheritDoc}
     */
    public function buildNewCollection(array $children) : ITypedCollection
    {
        return $this->mapper->buildCollection($children);
    }

    /**
     * {@inheritDoc}
     */
    public function loadCollectionValues(LoadingContext $context, array $rows) : array
    {
        return $this->mapper->loadAll($context, $rows);
    }

    // NOT USED:

    public function getIdFromValue($childValue)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function syncRelated(PersistenceContext $context, array $modifiedColumns, array $children) : array
    {
        throw NotImplementedException::method(__METHOD__);
    }
}