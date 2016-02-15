<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Locking;

use Dms\Core\Model\ITypedObject;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Row;

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