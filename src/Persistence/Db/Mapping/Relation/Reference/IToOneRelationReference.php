<?php

namespace Dms\Core\Persistence\Db\Mapping\Relation\Reference;

use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\Schema\Column;

/**
 * The to-one relation reference type interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IToOneRelationReference extends IRelationReference
{
    /**
     * @param LoadingContext $context
     * @param Row[]          $rows
     *
     * @return array
     */
    public function loadValues(LoadingContext $context, array $rows);

    /**
     * @param PersistenceContext $context
     * @param Column[]           $modifiedColumns
     * @param array              $children
     *
     * @return Row[]
     */
    public function syncRelated(PersistenceContext $context, array $modifiedColumns, array $children);
}