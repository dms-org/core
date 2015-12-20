<?php

namespace Dms\Core\Persistence\Db\Mapping\Relation\Reference;

use Dms\Core\Model\ITypedCollection;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\Schema\Column;

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
     * @param Column[]           $modifiedColumns
     * @param array              $children
     *
     * @return Row[]
     */
    public function syncRelated(PersistenceContext $context, array $modifiedColumns, array $children);
}