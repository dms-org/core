<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\Relation;

use Iddigital\Cms\Core\Exception\NotImplementedException;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\TypedCollection;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\ReadModelMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\IToManyRelationReference;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\IToOneRelationReference;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\RelationObjectReference;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;

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
     * {@inheritDoc}
     */
    public function loadValues(LoadingContext $context, array $rows)
    {
        return $this->mapper->loadAll($context, $rows);
    }

    /**
     * {@inheritDoc}
     */
    public function buildNewCollection(array $children)
    {
        return new TypedCollection(Type::object($this->mapper->getObjectType()), $children);
    }

    /**
     * {@inheritDoc}
     */
    public function loadCollectionValues(LoadingContext $context, array $rows)
    {
        return $this->mapper->loadAll($context, $rows);
    }

    // NOT USED:

    public function getIdFromValue($childValue)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function syncRelated(PersistenceContext $context, Column $foreignKeyToParent = null, array $children)
    {
        throw NotImplementedException::method(__METHOD__);
    }
}