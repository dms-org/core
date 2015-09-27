<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Locking;

use Iddigital\Cms\Core\Model\ITypedObject;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Util\DateTimeClock;
use Iddigital\Cms\Core\Util\IClock;

/**
 * Implements optimistic locking via a version property that
 * contains the last UTC time the entity was updated.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateTimeVersionLockingStrategy extends SinglePropertyLockingStrategy
{
    /**
     * @var IClock
     */
    private $clock;

    /**
     * @inheritDoc
     */
    public function __construct($propertyName, $columnName)
    {
        parent::__construct($propertyName, $columnName);
        $this->clock = new DateTimeClock();
    }

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
        $now        = $this->clock->utcNow();

        foreach ($rows as $row) {
            $currentVersion = $row->getColumn($columnName);
            $row->setColumn($columnName, $now);

            if ($row->hasPrimaryKey()) {
                $row->setLockingColumn($columnName, $currentVersion);
            }
        }
    }

    /**
     * For testing.
     *
     * @param IClock $clock
     *
     * @return void
     */
    public function setClock(IClock $clock)
    {
        $this->clock = $clock;
    }
}