<?php

namespace Dms\Core\Tests\Persistence\Db\Mock;

use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Persistence\Db\Connection\Connection;
use Dms\Core\Persistence\Db\Connection\IQuery;
use Dms\Core\Persistence\Db\Query\BulkUpdate;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\ResequenceOrderIndexColumn;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Query\Update;
use Dms\Core\Persistence\Db\Query\Upsert;
use Dms\Core\Persistence\Db\RowSet;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MockConnection extends Connection
{
    /**
     * @var IQuery[]
     */
    protected $queryLog = [];

    /**
     * @var MockPlatform
     */
    protected $platform;

    /**
     * @var MockDatabase
     */
    protected $db;

    /**
     * @var array
     */
    protected $preparedStatementResult;

    /**
     * @inheritDoc
     */
    public function __construct(MockDatabase $db)
    {
        parent::__construct(new MockPlatform());
        $this->db = $db;
    }

    /**
     * @return MockDatabase
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param MockDatabase $db
     */
    public function setDb(MockDatabase $db)
    {
        $this->db = $db;
    }

    /**
     * @return IQuery[]
     */
    public function getQueryLog()
    {
        return $this->queryLog;
    }

    /**
     * @return void
     */
    public function clearQueryLog()
    {
        $this->queryLog = [];
    }

    /**
     * @inheritDoc
     */
    public function load(Select $query) : RowSet
    {
        $this->queryLog[] = $query;
        $results          = $this->db->query($this->platform->compileSelect($query));

        return $this->platform->mapResultSetToPhpForm($query->getResultSetTableStructure(), $results);
    }

    /**
     * @inheritDoc
     */
    public function update(Update $query) : int
    {
        $this->queryLog[] = $query;

        return $this->db->query($this->platform->compileUpdate($query));
    }

    /**
     * @inheritDoc
     */
    public function delete(Delete $query) : int
    {
        $this->queryLog[] = $query;

        return $this->db->query($this->platform->compileDelete($query));
    }

    /**
     * @inheritDoc
     */
    public function resequenceOrderIndexColumn(ResequenceOrderIndexColumn $query)
    {
        $this->queryLog[] = $query;

        return $this->db->query($this->platform->compileResequenceOrderIndexColumn($query));
    }

    /**
     * @inheritDoc
     */
    public function upsert(Upsert $query)
    {
        $this->queryLog[] = $query;
        parent::upsert($query);
    }

    /**
     * @inheritDoc
     */
    public function bulkUpdate(BulkUpdate $query)
    {
        $this->queryLog[] = $query;
        parent::bulkUpdate($query);
    }

    public function getLastInsertId() : int
    {
        return $this->db->getLastInsertId();
    }

    /**
     * Begins a transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        $this->db->beginTransaction();
    }

    /**
     * Returns whether the connection is in a transaction.
     *
     * @return bool
     */
    public function isInTransaction() : bool
    {
        return $this->db->isInTransaction();
    }

    /**
     * Commits the transaction.
     *
     * @return void
     */
    public function commitTransaction()
    {
        $this->db->commitTransaction();
    }

    /**
     * Rollsback the transaction.
     *
     * @return void
     */
    public function rollbackTransaction()
    {
        $this->db->rollbackTransaction();
    }

    public function setPreparedStatementResult(array $rows = null)
    {
        $this->preparedStatementResult = $rows;
    }

    public function prepare($sql, array $parameters = []) : IQuery
    {
        if ($sql instanceof PhpPreparedCompiledQuery) {
            $sql->setConnection($this);
            $sql->setParameters($parameters);

            return $sql;
        }

        if ($this->preparedStatementResult === null) {
            throw NotImplementedException::method(__METHOD__);
        } else {
            $this->queryLog[] = [$sql, $parameters];

            return new MockQuery($this->preparedStatementResult);
        }
    }
}