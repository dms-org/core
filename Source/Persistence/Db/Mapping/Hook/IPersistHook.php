<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Hook;

use Iddigital\Cms\Core\Model\ITypedObject;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Row;

/**
 * The persist hook interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IPersistHook
{
    /**
     * @param PersistenceContext $context
     * @param ITypedObject[]     $objects
     * @param Row[]              $rows
     *
     * @return void
     */
    public function fireBeforeCommit(PersistenceContext $context, array $objects, array $rows);

    /**
     * @param PersistenceContext $context
     * @param ITypedObject[]     $objects
     * @param Row[]              $rows
     *
     * @return void
     */
    public function fireAfterCommit(PersistenceContext $context, array $objects, array $rows);

    /**
     * @param string $prefix
     *
     * @return static
     */
    public function withColumnNamesPrefixedBy($prefix);
}