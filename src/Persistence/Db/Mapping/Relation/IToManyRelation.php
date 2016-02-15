<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Relation;

use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Model\ITypedCollection;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\ParentChildrenMap;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\IToManyRelationReference;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Select;

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
    public function buildCollection(array $children) : \Dms\Core\Model\ITypedCollection;

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