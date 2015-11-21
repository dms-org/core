<?php

namespace Iddigital\Cms\Core\Persistence\Db\Connection\Dummy;

use Iddigital\Cms\Core\Exception\NotImplementedException;
use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\Db\Query\BulkUpdate;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\ResequenceOrderIndexColumn;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Query\Update;
use Iddigital\Cms\Core\Persistence\Db\Query\Upsert;
use Iddigital\Cms\Core\Persistence\Db\RowSet;

/**
 * The mock connection class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DummyConnection implements IConnection
{
    /**
     * @inheritDoc
     */
    public function getPlatform()
    {
        throw NotImplementedException::method(__METHOD__);
    }

    /**
     * @inheritDoc
     */
    public function getLastInsertId()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function beginTransaction()
    {

    }

    /**
     * @inheritDoc
     */
    public function isInTransaction()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function commitTransaction()
    {

    }

    /**
     * @inheritDoc
     */
    public function withinTransaction(callable $operation)
    {
        $operation();
    }

    /**
     * @inheritDoc
     */
    public function rollbackTransaction()
    {

    }

    /**
     * @inheritDoc
     */
    public function prepare($sql, array $parameters = [])
    {
        throw NotImplementedException::method(__METHOD__);
    }

    /**
     * @inheritDoc
     */
    public function load(Select $query)
    {
        return new RowSet($query->getResultSetTableStructure());
    }

    /**
     * @inheritDoc
     */
    public function update(Update $query)
    {

    }

    /**
     * @inheritDoc
     */
    public function delete(Delete $query)
    {

    }

    /**
     * @inheritDoc
     */
    public function upsert(Upsert $query)
    {

    }

    /**
     * @inheritDoc
     */
    public function bulkUpdate(BulkUpdate $query)
    {

    }

    /**
     * @inheritDoc
     */
    public function resequenceOrderIndexColumn(ResequenceOrderIndexColumn $query)
    {

    }

}