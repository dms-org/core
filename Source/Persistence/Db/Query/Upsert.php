<?php

namespace Dms\Core\Persistence\Db\Query;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\RowSet;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Util\Debug;

/**
 * The upsert query.
 *
 * This should insert the rows without primary keys and
 * update the rows with set primary keys or if no rows with
 * the corresponding primary key is found, an exception should be thrown.
 *
 * If optimistic locking is used, the updated rows should
 * also add a check to ensure the rows match the current version
 * data, if not throw an exception.
 *
 * @see    DbOutOfSyncException
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Upsert implements IQuery
{
    /**
     * @var RowSet
     */
    protected $rowsWithoutPrimaryKeys;

    /**
     * @var RowSet
     */
    protected $rowsWithPrimaryKeys;

    /**
     * @var string[]
     */
    protected $lockingColumnNames = [];

    /**
     * @inheritDoc
     */
    public function __construct(RowSet $rows, array $lockingColumnNames = [])
    {
        $this->rowsWithoutPrimaryKeys = $rows->getRowsWithoutPrimaryKeys();
        $this->rowsWithPrimaryKeys    = $rows->getRowsWithPrimaryKeys();

        $table = $rows->getTable();

        foreach ($lockingColumnNames as $lockingColumnName) {
            if (!$table->hasColumn($lockingColumnName)) {
                throw InvalidArgumentException::format(
                        'Invalid call to %s: locking columns must be one of (%s), %s given',
                        __METHOD__, Debug::formatValues($table->getColumnNames()), $lockingColumnName
                );
            }
        }

        $this->lockingColumnNames = $lockingColumnNames;
    }


    /**
     * {@inheritdoc}
     */
    public function executeOn(IConnection $connection)
    {
        $connection->upsert($this);
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->rowsWithPrimaryKeys->getTable();
    }

    /**
     * @return RowSet
     */
    public function getRowsWithPrimaryKeys()
    {
        return $this->rowsWithPrimaryKeys;
    }

    /**
     * @return RowSet
     */
    public function getRowsWithoutPrimaryKeys()
    {
        return $this->rowsWithoutPrimaryKeys;
    }

    /**
     * @return string[]
     */
    public function getLockingColumnNames()
    {
        return $this->lockingColumnNames;
    }
}