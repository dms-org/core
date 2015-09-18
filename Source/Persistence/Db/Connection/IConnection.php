<?php

namespace Iddigital\Cms\Core\Persistence\Db\Connection;

use Iddigital\Cms\Core\Persistence\Db\Platform\IPlatform;
use Iddigital\Cms\Core\Persistence\Db\Query\BulkUpdate;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Query\Update;
use Iddigital\Cms\Core\Persistence\Db\Query\Upsert;
use Iddigital\Cms\Core\Persistence\Db\RowSet;

/**
 * The db connection interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IConnection
{
    /**
     * Gets the database platform
     *
     * @return IPlatform
     */
    public function getPlatform();

    /**
     * Gets the last insert id.
     *
     * @return int
     */
    public function getLastInsertId();

    /**
     * Begins a transaction.
     *
     * @return void
     */
    public function beginTransaction();

    /**
     * Returns whether the connection is in a transaction.
     *
     * @return bool
     */
    public function isInTransaction();

    /**
     * Commits the transaction.
     *
     * @return void
     */
    public function commitTransaction();

    /**
     * Runs the supplied callable in a transaction.
     * If the connection is already in a transaction it will
     * be run in the current transaction.
     *
     * If there is not current transaction, a new transaction will be started.
     * If an exception is thrown this transaction will be rolled back.
     * If not this transaction will be committed.
     *
     * @param callable $operation
     *
     * @return mixed
     */
    public function withinTransaction(callable $operation);

    /**
     * Rollsback the transaction.
     *
     * @return void
     */
    public function rollbackTransaction();

    /**
     * Creates a query with the specified sql and parameters.
     *
     * @param string $sql
     * @param array  $parameters
     *
     * @return IQuery
     */
    public function prepare($sql, array $parameters = []);

    /**
     * Loads the result set from the supplied select query.
     *
     * @param Select $query
     *
     * @return RowSet
     */
    public function load(Select $query);

    /**
     * Performs the update query and returns the number of affected rows.
     *
     * @param Update $query
     *
     * @return int
     */
    public function update(Update $query);

    /**
     * Performs the delete query and returns the number of affected rows.
     *
     * @param Delete $query
     *
     * @return int
     */
    public function delete(Delete $query);

    /**
     * Performs the upsert query. Rows without primary keys are expected to
     * fire the primary key set callbacks.
     *
     * @param Upsert $query
     *
     * @return void
     */
    public function upsert(Upsert $query);

    /**
     * Updates the rows with the supplied column data.
     *
     * @param BulkUpdate $query
     *
     * @return void
     */
    public function bulkUpdate(BulkUpdate $query);
}