<?php

namespace Dms\Core\Persistence\Db\Mapping\Relation\Reference;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\Schema\Column;

/**
 * The to-one relation id reference class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToOneRelationIdentityReference extends RelationIdentityReference implements IToOneRelationReference
{
    /**
     * @return ToOneRelationObjectReference
     */
    public function asObjectReference()
    {
        return new ToOneRelationObjectReference($this->mapper, $this->bidirectionalRelationProperty);
    }

    /**
     * @param LoadingContext $context
     * @param Row[]          $rows
     *
     * @return array
     */
    public function loadValues(LoadingContext $context, array $rows)
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
     * @throws InvalidArgumentException
     */
    public function syncRelated(PersistenceContext $context, array $modifiedColumns, array $children)
    {
        return $this->bulkUpdateForeignKeys($context, $modifiedColumns, $children);
    }
}