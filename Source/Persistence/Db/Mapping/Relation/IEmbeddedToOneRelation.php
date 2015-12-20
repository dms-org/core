<?php

namespace Dms\Core\Persistence\Db\Mapping\Relation;

use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Persistence\Db\Mapping\ParentChildMap;
use Dms\Core\Persistence\Db\PersistenceContext;

/**
 * The embedded to one relation interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IEmbeddedToOneRelation extends IToOneRelation, IEmbeddedRelation
{
    /**
     * @param PersistenceContext $context
     * @param ParentChildMap     $map
     *
     * @return void
     * @throws TypeMismatchException
     */
    public function persistBeforeParent(PersistenceContext $context, ParentChildMap $map);

    /**
     * @param PersistenceContext $context
     * @param ParentChildMap     $map
     *
     * @return void
     * @throws TypeMismatchException
     */
    public function persistAfterParent(PersistenceContext $context, ParentChildMap $map);
}