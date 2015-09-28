<?php

namespace Iddigital\Cms\Core\Persistence\Db\Connection;

use Iddigital\Cms\Core\Exception\BaseException;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Row;

/**
 * The exception for when the database is out-of-sync
 * when rows are being persisted.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DbOutOfSyncException extends BaseException
{
    /**
     * @var Row
     */
    private $rowBeingPersisted;

    /**
     * @var Row|null
     */
    private $currentRowInDb;

    /**
     * DbOutOfSyncException constructor.
     *
     * @param Row      $rowBeingPersisted
     * @param Row|null $currentRowInDb
     *
     * @throws InvalidArgumentException
     */
    public function __construct(Row $rowBeingPersisted, Row $currentRowInDb = null)
    {
        parent::__construct(sprintf(
                'Could not persist row on table %s with primary key %d: the row has been %s in another instance',
                $rowBeingPersisted->getTable()->getName(), $rowBeingPersisted->getPrimaryKey(),
                $currentRowInDb ? 'updated' : 'deleted'
        ));

        $this->rowBeingPersisted = $rowBeingPersisted;
        $this->currentRowInDb    = $currentRowInDb;
    }

    /**
     * @return Row
     */
    public function getRowBeingPersisted()
    {
        return $this->rowBeingPersisted;
    }

    /**
     * @return Row|null
     */
    public function getCurrentRowInDb()
    {
        return $this->currentRowInDb;
    }
}