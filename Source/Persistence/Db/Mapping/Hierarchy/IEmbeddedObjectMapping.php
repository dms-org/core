<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Hierarchy;

use Iddigital\Cms\Core\Model\ITypedObject;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Row;

/**
 * The embedded class mapping interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IEmbeddedObjectMapping extends IObjectMapping
{
    /**
     * @param PersistenceContext $context
     * @param ITypedObject[]     $objects
     * @param Row[]              $rows
     */
    public function persistAllBeforeParent(PersistenceContext $context, array $objects, array $rows);

    /**
     * @param PersistenceContext $context
     * @param ITypedObject[]     $objects
     * @param Row[]              $rows
     */
    public function persistAllAfterParent(PersistenceContext $context, array $objects, array $rows);

    /**
     * @param PersistenceContext $context
     * @param Delete             $deleteQuery
     */
    public function deleteBeforeParent(PersistenceContext $context, Delete $deleteQuery);

    /**
     * @param PersistenceContext $context
     * @param Delete             $deleteQuery
     */
    public function deleteAfterParent(PersistenceContext $context, Delete $deleteQuery);
}