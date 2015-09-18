<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference;

use Iddigital\Cms\Core\Model\ITypedCollection;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;

/**
 * The to-many relation reference type interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IToManyRelationReference extends IRelationReference
{
    /**
     * @param array $children
     *
     * @return ITypedCollection
     */
    public function buildNewCollection(array $children);

    /**
     * @param LoadingContext $context
     * @param Row[]          $rows
     *
     * @return array
     */
    public function loadCollectionValues(LoadingContext $context, array $rows);

    /**
     * @param PersistenceContext $context
     * @param Column|null        $foreignKeyToParent
     * @param array              $children
     *
     * @return Row[]
     */
    public function syncRelated(PersistenceContext $context, Column $foreignKeyToParent = null, array $children);
}