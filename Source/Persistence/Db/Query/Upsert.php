<?php

namespace Iddigital\Cms\Core\Persistence\Db\Query;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\Db\RowSet;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The upsert query.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Upsert extends RowSetQuery
{
    /**
     * @var string[]
     */
    protected $lockingColumnNames = [];

    /**
     * @inheritDoc
     */
    public function __construct(RowSet $rows, array $lockingColumnNames = [])
    {
        parent::__construct($rows);

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
     * @return string[]
     */
    public function getLockingColumnNames()
    {
        return $this->lockingColumnNames;
    }
}