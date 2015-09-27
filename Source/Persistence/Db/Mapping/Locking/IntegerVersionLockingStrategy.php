<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Locking;

use Iddigital\Cms\Core\Model\ITypedObject;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Row;

/**
 * Implements optimistic locking via a version property that
 * contains an integer that will be incremented every persist.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IntegerVersionLockingStrategy extends SinglePropertyLockingStrategy
{
    /**
     * @param PersistenceContext $context
     * @param ITypedObject[]     $objects
     * @param Row[]              $rows
     *
     * @return mixed
     */
    public function applyLockingDataBeforeCommit(PersistenceContext $context, array $objects, array $rows)
    {
        $columnName = $this->columnName;

        foreach ($rows as $row) {
            $currentVersion = $row->getColumn($columnName);
            $row->setColumn($columnName, $currentVersion + 1);

            if ($row->hasPrimaryKey()) {
                $row->setLockingColumn($columnName, $currentVersion);
            }
        }
    }
}