<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Hook;

use Dms\Core\Model\ITypedObject;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Row;

/**
 * The persist hook interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IPersistHook
{
    /**
     * Gets a unique string to represent this persist hook.
     *
     * @return string
     */
    public function getIdString() : string;

    /**
     * @param PersistenceContext $context
     * @param ITypedObject[]     $objects
     * @param Row[]              $rows
     *
     * @return void
     */
    public function fireBeforePersist(PersistenceContext $context, array $objects, array $rows);

    /**
     * @param PersistenceContext $context
     * @param ITypedObject[]     $objects
     * @param Row[]              $rows
     *
     * @return void
     */
    public function fireAfterPersist(PersistenceContext $context, array $objects, array $rows);

    /**
     * @param PersistenceContext $context
     * @param Delete             $deleteQuery
     *
     * @return void
     */
    public function fireBeforeDelete(PersistenceContext $context, Delete $deleteQuery);

    /**
     * @param PersistenceContext $context
     * @param Delete             $deleteQuery
     *
     * @return void
     */
    public function fireAfterDelete(PersistenceContext $context, Delete $deleteQuery);

    /**
     * @param string $prefix
     *
     * @return static
     */
    public function withColumnNamesPrefixedBy(string $prefix);
}