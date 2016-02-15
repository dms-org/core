<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Relation\Reference;

use Dms\Core\Model\EntityIdCollection;
use Dms\Core\Model\ITypedCollection;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\Schema\Column;

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
    public function asObjectReference() : RelationObjectReference
    {
        return new ToManyRelationObjectReference($this->mapper, $this->bidirectionalRelationProperty);
    }

    /**
     * @param array $children
     *
     * @return ITypedCollection
     */
    public function buildNewCollection(array $children) : \Dms\Core\Model\ITypedCollection
    {
        return new EntityIdCollection($children);
    }

    /**
     * @param LoadingContext $context
     * @param Row[]          $rows
     *
     * @return array
     */
    public function loadCollectionValues(LoadingContext $context, array $rows) : array
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
    public function syncRelated(PersistenceContext $context, array $modifiedColumns, array $children) : array
    {
        return $this->bulkUpdateForeignKeys($context, $modifiedColumns, $children);
    }
}