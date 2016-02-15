<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Relation\Reference;

use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\Schema\Column;

/**
 * The to-one relation object reference class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToOneRelationObjectReference extends RelationObjectReference implements IToOneRelationReference
{
    /**
     * @param LoadingContext $context
     * @param Row[]          $rows
     *
     * @return array
     */
    public function loadValues(LoadingContext $context, array $rows) : array
    {
        return $this->mapper->loadAll($context, $rows);
    }

    /**
     * @param PersistenceContext $context
     * @param Column[]           $modifiedColumns
     * @param array              $children
     *
     * @return int[]
     */
    public function syncRelated(PersistenceContext $context, array $modifiedColumns, array $children) : array
    {
        return $this->persistChildrenIgnoringBidirectionalRelation($context, $children);
    }
}