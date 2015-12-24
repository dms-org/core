<?php

namespace Dms\Core\Persistence\Db\Mapping\Locking;

use Dms\Core\Model\ITypedObject;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Row;

/**
 * The optimistic locking strategy interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IOptimisticLockingStrategy
{
    /**
     * @return string[]
     */
    public function getLockingColumnNames();

    /**
     * @param PersistenceContext $context
     * @param ITypedObject[]     $objects
     * @param Row[]              $rows
     *
     * @return void
     */
    public function applyLockingDataBeforeCommit(PersistenceContext $context, array $objects, array $rows);

    /**
     * @param PersistenceContext $context
     * @param ITypedObject[]     $objects
     * @param Row[]              $rows
     *
     * @return void
     */
    public function applyLockingDataAfterCommit(PersistenceContext $context, array $objects, array $rows);

    /**
     * @param string $prefix
     *
     * @return static
     */
    public function withColumnNamesPrefixedBy($prefix);
}