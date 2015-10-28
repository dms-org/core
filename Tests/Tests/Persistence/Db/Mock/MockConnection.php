<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Mock;

use Iddigital\Cms\Core\Exception\NotImplementedException;
use Iddigital\Cms\Core\Persistence\Db\Connection\Connection;
use Iddigital\Cms\Core\Persistence\Db\Query\BulkUpdate;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\ResequenceOrderIndexColumn;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Query\Update;
use Iddigital\Cms\Core\Persistence\Db\Query\Upsert;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MockConnection extends Connection
{
    /**
     * @var array
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
     * @return array
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
    public function load(Select $query)
    {
        $this->queryLog[] = $query;
        $results = $this->db->query($this->platform->compileSelect($query));

        return $this->platform->mapResultSetToPhpForm($query->getResultSetTableStructure(), $results);
    }

    /**
     * @inheritDoc
     */
    public function update(Update $query)
    {
        $this->queryLog[] = $query;
        return $this->db->query($this->platform->compileUpdate($query));
    }

    /**
     * @inheritDoc
     */
    public function delete(Delete $query)
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

    public function getLastInsertId()
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
    public function isInTransaction()
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

    public function prepare($sql, array $parameters = [])
    {
        if ($sql instanceof PhpPreparedCompiledQuery) {
            $sql->setConnection($this);
            $sql->setParameters($parameters);

            return $sql;
        }

        throw NotImplementedException::method(__METHOD__);
    }
}