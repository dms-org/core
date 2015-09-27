<?php

namespace Iddigital\Cms\Core\Persistence\Db\Connection;

use Iddigital\Cms\Core\Persistence\Db\Platform\CompiledQuery;
use Iddigital\Cms\Core\Persistence\Db\Platform\IPlatform;
use Iddigital\Cms\Core\Persistence\Db\Query\BulkUpdate;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Query\Update;
use Iddigital\Cms\Core\Persistence\Db\Query\Upsert;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The connection base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Connection implements IConnection
{
    /**
     * @var IPlatform
     */
    protected $platform;

    /**
     * Connection constructor.
     *
     * @param IPlatform $platform
     */
    public function __construct(IPlatform $platform)
    {
        $this->platform = $platform;
    }

    /**
     * @return IPlatform
     */
    final public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @param callable $operation
     *
     * @return mixed
     * @throws \Exception
     */
    public function withinTransaction(callable $operation)
    {
        if ($this->isInTransaction()) {
            return $operation();
        }

        $this->beginTransaction();
        try {
            $result = $operation();
            $this->commitTransaction();

            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * @param CompiledQuery $compiledQuery
     *
     * @return IQuery
     */
    protected function loadQueryFrom(CompiledQuery $compiledQuery)
    {
        return $this->prepare($compiledQuery->getSql(), $compiledQuery->getParameters());
    }

    /**
     *{@inheritDoc}
     */
    public function load(Select $query)
    {
        $compiled = $this->loadQueryFrom($this->platform->compileSelect($query));
        $compiled->execute();

        return $this->platform->mapResultSetToPhpForm($query->getResultSetTableStructure(), $compiled->getResults());
    }

    /**
     *{@inheritDoc}
     */
    public function update(Update $query)
    {
        $compiled = $this->loadQueryFrom($this->platform->compileUpdate($query));
        $compiled->execute();

        return $compiled->getAffectedRows();
    }

    /**
     *{@inheritDoc}
     */
    public function delete(Delete $query)
    {
        $compiled = $this->loadQueryFrom($this->platform->compileDelete($query));
        $compiled->execute();

        return $compiled->getAffectedRows();
    }

    /**
     *{@inheritDoc}
     */
    public function upsert(Upsert $query)
    {
        $this->withinTransaction(function () use ($query) {
            $rows                     = $query->getRows();
            $table                    = $rows->getTable();
            $isUsingOptimisticLocking = count($query->getLockingColumnNames()) > 0;

            $insert = $this->prepare($this->platform->compilePreparedInsert($table));

            $rowsWithKeys     = $rows->getRowsWithPrimaryKeys();
            $rowsWithKeyArray = $rowsWithKeys->getRows();
            $rowsData         = $this->platform->mapResultSetToDbFormat($rowsWithKeys, 'lock__');
            if ($rowsData) {
                $lockingColumnNameParameterMap = [];

                foreach ($query->getLockingColumnNames() as $columnName) {
                    $lockingColumnNameParameterMap[$columnName] = 'lock__' . $columnName;
                }

                $update = $this->createPreparedUpdatedWithWhereId($table, $lockingColumnNameParameterMap);

                foreach ($rowsData as $key => $row) {
                    $update->setParameters($row);
                    $update->execute();

                    // If the update does not succeed that means the row has been updated
                    // and optimistic locking has failed OR the row with the primary key
                    // no longer exists.
                    if ($update->getAffectedRows() !== 1) {
                        if ($isUsingOptimisticLocking) {
                            // If optimistic locking is used, we get the current row
                            // and throw an exception.
                            $currentRow = $this->load(
                                    Select::allFrom($table)
                                            ->where(Expr::equal(
                                                    Expr::tableColumn($table, $table->getPrimaryKeyColumnName()),
                                                    Expr::idParam($rowsWithKeyArray[$key]->getColumn($table->getPrimaryKeyColumnName()))
                                            ))
                            )->getFirstRowOrNull();

                            throw new DbOutOfSyncException(
                                    $rowsWithKeyArray[$key],
                                    $currentRow
                            );
                        } else {
                            $insert->setParameters($row);
                            $insert->execute();
                        }
                    }
                }
            }

            $rowsWithoutKeys = $rows->getRowsWithoutPrimaryKeys();
            $rowArray        = $rowsWithoutKeys->getRows();
            $rowsData        = $this->platform->mapResultSetToDbFormat($rowsWithoutKeys);

            if ($rowsData) {
                foreach ($rowsData as $key => $row) {
                    $insert->setParameters($row);
                    $insert->execute();
                    $rowArray[$key]->firePrimaryKeyCallbacks($this->getLastInsertId());
                }
            }
        });
    }

    /**
     * {@inheritDoc}
     */
    public function bulkUpdate(BulkUpdate $query)
    {
        $this->withinTransaction(function () use ($query) {
            $rows     = $query->getRows();
            $rowArray = $rows->getRows();
            $table    = $rows->getTable();

            $rowsData = $this->platform->mapResultSetToDbFormat($rows);

            $update = $this->createPreparedUpdatedWithWhereId($table);

            foreach ($rowsData as $key => $row) {
                $update->setParameters($row);
                $update->execute();

                if ($update->getAffectedRows() !== 1) {
                    throw new DbOutOfSyncException(
                            $rowArray[$key],
                            null // There is no row with the supplied id.
                    );
                }
            }
        });
    }

    protected function createPreparedUpdatedWithWhereId(Table $table, array $lockingColumnNameParameterMap = [])
    {
        $primaryKey = $table->getPrimaryKeyColumnName();

        return $this->prepare($this->platform->compilePreparedUpdate(
                $table,
                array_diff($table->getColumnNames(), [$primaryKey]),
                $lockingColumnNameParameterMap + [$primaryKey => $primaryKey]
        ));
    }
}