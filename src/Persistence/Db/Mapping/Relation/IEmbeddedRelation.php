<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Relation;

use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;

/**
 * The embedded relation interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IEmbeddedRelation extends IRelation
{
    /**
     * @param PersistenceContext $context
     * @param Delete             $parentDelete
     *
     * @return void
     */
    public function deleteBeforeParent(PersistenceContext $context, Delete $parentDelete);

    /**
     * @param PersistenceContext $context
     * @param Delete             $parentDelete
     *
     * @return void
     */
    public function deleteAfterParent(PersistenceContext $context, Delete $parentDelete);
}