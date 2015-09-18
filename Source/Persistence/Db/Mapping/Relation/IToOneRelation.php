<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation;

use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ParentChildMap;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\IToOneRelationReference;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;

/**
 * The to one relation interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IToOneRelation extends IRelation
{
    /**
     * Returns an equivalent relation with the supplied reference type.
     *
     * @param IToOneRelationReference $reference
     *
     * @return static
     */
    public function withReference(IToOneRelationReference $reference);

    /**
     * @param PersistenceContext $context
     * @param ParentChildMap     $map
     *
     * @return void
     * @throws TypeMismatchException
     */
    public function persist(PersistenceContext $context, ParentChildMap $map);

    /**
     * @param LoadingContext $context
     * @param ParentChildMap $map
     *
     * @return void
     */
    public function load(LoadingContext $context, ParentChildMap $map);
}