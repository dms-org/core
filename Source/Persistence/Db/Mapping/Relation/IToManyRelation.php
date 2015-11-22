<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation;

use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Model\ITypedCollection;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ParentChildrenMap;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\IToManyRelationReference;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;

/**
 * The to many relation interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IToManyRelation extends IRelation
{
    /**
     * Returns an equivalent relation with the supplied reference type.
     *
     * @param IToManyRelationReference $reference
     *
     * @return static
     */
    public function withReference(IToManyRelationReference $reference);

    /**
     * @param array $children
     *
     * @return ITypedCollection
     */
    public function buildCollection(array $children);

    /**
     * @param PersistenceContext $context
     * @param ParentChildrenMap  $map
     *
     * @return void
     * @throws TypeMismatchException
     */
    public function persist(PersistenceContext $context, ParentChildrenMap $map);

    /**
     * @param LoadingContext    $context
     * @param ParentChildrenMap $map
     *
     * @return mixed
     */
    public function load(LoadingContext $context, ParentChildrenMap $map);
}