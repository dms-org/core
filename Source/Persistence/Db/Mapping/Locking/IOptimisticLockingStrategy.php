<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Locking;

use Iddigital\Cms\Core\Model\ITypedObject;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Row;

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
     * @return mixed
     */
    public function applyLockingDataBeforeCommit(PersistenceContext $context, array $objects, array $rows);

    /**
     * @param PersistenceContext $context
     * @param ITypedObject[]     $objects
     * @param Row[]              $rows
     *
     * @return mixed
     */
    public function applyLockingDataAfterCommit(PersistenceContext $context, array $objects, array $rows);

    /**
     * @param string $prefix
     *
     * @return static
     */
    public function withColumnNamesPrefixedBy($prefix);
}