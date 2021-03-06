<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Relation\Reference;

use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\ITypedCollection;
use Dms\Core\Model\Type\IType;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\Schema\Column;

/**
 * The to-many relation object reference class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToManyRelationObjectReference extends RelationObjectReference implements IToManyRelationReference
{
    /**
     * @inheritDoc
     */
    public function getCollectionType() : IType
    {
        return $this->mapper->getCollectionType();
    }

    /**
     * @param array $children
     *
     * @return ITypedCollection
     */
    public function buildNewCollection(array $children) : \Dms\Core\Model\ITypedCollection
    {
        return $this->mapper->buildCollection($children);
    }

    /**
     * @param LoadingContext $context
     * @param Row[]          $rows
     *
     * @return array
     */
    public function loadCollectionValues(LoadingContext $context, array $rows) : array
    {
        return $this->mapper->loadAll($context, $rows);
    }

    /**
     * @param PersistenceContext $context
     * @param Column[]           $modifiedColumns
     * @param array              $children
     *
     * @return Row[]
     */
    public function syncRelated(PersistenceContext $context, array $modifiedColumns, array $children) : array
    {
        return $this->persistChildrenIgnoringBidirectionalRelation($context, $children);
    }
}