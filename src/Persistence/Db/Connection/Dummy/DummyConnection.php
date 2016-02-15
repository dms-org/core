<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Connection\Dummy;

use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Connection\IQuery;
use Dms\Core\Persistence\Db\Platform\IPlatform;
use Dms\Core\Persistence\Db\Query\BulkUpdate;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\ResequenceOrderIndexColumn;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Query\Update;
use Dms\Core\Persistence\Db\Query\Upsert;
use Dms\Core\Persistence\Db\RowSet;

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
    public function getPlatform() : IPlatform
    {
        throw NotImplementedException::method(__METHOD__);
    }

    /**
     * @inheritDoc
     */
    public function getLastInsertId() : int
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
    public function isInTransaction() : bool
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
    public function prepare($sql, array $parameters = []) : IQuery
    {
        throw NotImplementedException::method(__METHOD__);
    }

    /**
     * @inheritDoc
     */
    public function load(Select $query) : RowSet
    {
        return new RowSet($query->getResultSetTableStructure());
    }

    /**
     * @inheritDoc
     */
    public function update(Update $query) : int
    {

    }

    /**
     * @inheritDoc
     */
    public function delete(Delete $query) : int
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