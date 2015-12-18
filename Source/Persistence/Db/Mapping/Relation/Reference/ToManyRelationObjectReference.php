<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference;

use Iddigital\Cms\Core\Model\EntityCollection;
use Iddigital\Cms\Core\Model\ITypedCollection;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;

/**
 * The to-many relation object reference class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToManyRelationObjectReference extends RelationObjectReference implements IToManyRelationReference
{
    /**
     * @param array $children
     *
     * @return ITypedCollection
     */
    public function buildNewCollection(array $children)
    {
        return $this->mapper->buildCollection($children);
    }

    /**
     * @param LoadingContext $context
     * @param Row[]          $rows
     *
     * @return array
     */
    public function loadCollectionValues(LoadingContext $context, array $rows)
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
    public function syncRelated(PersistenceContext $context, array $modifiedColumns, array $children)
    {
        return $this->persistChildrenIgnoringBidirectionalRelation($context, $children);
    }
}