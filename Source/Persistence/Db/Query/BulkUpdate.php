<?php

namespace Dms\Core\Persistence\Db\Query;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\RowSet;

/**
 * The bulk-update query.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class BulkUpdate extends RowSetQuery
{
    /**
     * @inheritDoc
     */
    public function __construct(RowSet $rows)
    {
        parent::__construct($rows);
    }

    /**
     * {@inheritdoc}
     */
    public function executeOn(IConnection $connection)
    {
        if ($this->rows->getRowsWithoutPrimaryKeys()->count() > 0) {
            throw new InvalidArgumentException('Cannot create bulk-update: row set contains rows without primary keys');
        }

        $connection->bulkUpdate($this);
    }
}