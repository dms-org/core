<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Hook;

use Iddigital\Cms\Core\Model\ITypedObject;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Row;

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
    public function getIdString();

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
    public function withColumnNamesPrefixedBy($prefix);
}