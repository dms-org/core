<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation;

use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ParentChildMap;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;

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