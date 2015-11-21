<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference;

use Iddigital\Cms\Core\Model\EntityIdCollection;
use Iddigital\Cms\Core\Model\ITypedCollection;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;

/**
 * The to-many relation identity reference class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToManyRelationIdentityReference extends RelationIdentityReference implements IToManyRelationReference
{
    /**
     * @return ToManyRelationObjectReference
     */
    public function asObjectReference()
    {
        return new ToManyRelationObjectReference($this->mapper, $this->bidirectionalRelationProperty);
    }

    /**
     * @param array $children
     *
     * @return ITypedCollection
     */
    public function buildNewCollection(array $children)
    {
        return new EntityIdCollection($children);
    }

    /**
     * @param LoadingContext $context
     * @param Row[]          $rows
     *
     * @return array
     */
    public function loadCollectionValues(LoadingContext $context, array $rows)
    {
        $primaryKey = $this->primaryKeyColumn->getName();
        $ids        = [];

        foreach ($rows as $key => $row) {
            $ids[$key] = $row->getColumn($primaryKey);
        }

        return $ids;
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
        return $this->bulkUpdateForeignKeys($context, $modifiedColumns, $children);
    }
}