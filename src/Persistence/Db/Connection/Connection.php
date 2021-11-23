<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Connection;

use Dms\Core\Persistence\Db\Platform\CompiledQuery;
use Dms\Core\Persistence\Db\Platform\IPlatform;
use Dms\Core\Persistence\Db\Query\BulkUpdate;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\ResequenceOrderIndexColumn;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Query\Update;
use Dms\Core\Persistence\Db\Query\Upsert;
use Dms\Core\Persistence\Db\RowSet;
use Dms\Core\Persistence\Db\Schema\Table;

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
    final public function getPlatform() : IPlatform
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
    protected function loadQueryFrom(CompiledQuery $compiledQuery) : IQuery
    {
        return $this->prepare($compiledQuery->getSql(), $compiledQuery->getParameters());
    }

    /**
     *{@inheritDoc}
     */
    public function load(Select $query) : RowSet
    {
        $compiled = $this->loadQueryFrom($this->platform->compileSelect($query));
        $compiled->execute();

        return $this->platform->mapResultSetToPhpForm($query->getResultSetTableStructure(), $compiled->getResults());
    }

    /**
     *{@inheritDoc}
     */
    public function update(Update $query) : int
    {
        $compiled = $this->loadQueryFrom($this->platform->compileUpdate($query));
        $compiled->execute();

        return $compiled->getAffectedRows();
    }

    /**
     *{@inheritDoc}
     */
    public function delete(Delete $query) : int
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
            $table = $query->getTable();

            $rowsWithKeys     = $query->getRowsWithPrimaryKeys();
            $rowsWithKeyArray = $rowsWithKeys->getRows();
            $rowsData         = $this->platform->mapResultSetToDbFormat($rowsWithKeys, 'lock__');
;
            if ($rowsData) {
                $lockingColumnNameParameterMap = [];

                foreach ($query->getLockingColumnNames() as $columnName) {
                    $lockingColumnNameParameterMap[$columnName] = 'lock__' . $columnName;
                }

                $groupedRowData = $this->groupRowDataBySuppliedColumns($rowsData);

                foreach ($groupedRowData as $rows) {
                    $columnsToUpdate = array_diff(array_keys($rows[0]), $lockingColumnNameParameterMap);
                    $update          = $this->createPreparedUpdatedWithWhereId($table, $columnsToUpdate, $lockingColumnNameParameterMap);

                    foreach ($rows as $key => $row) {
                        $update->setParameters($row);
                        $update->execute();

                        // If the update does not succeed that means the row has been updated
                        // and optimistic locking has failed OR the row with that primary key
                        // no longer exists.
                        if ($update->getAffectedRows() !== 1) {
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
                        }
                    }
                }
            }

            $rowsWithoutKeys = $query->getRowsWithoutPrimaryKeys();
            $rowArray        = $rowsWithoutKeys->getRows();
            $rowsData        = $this->platform->mapResultSetToDbFormat($rowsWithoutKeys);
            $defaultData     = $table->getNullColumnData();
            $primaryKeyToRemove = !$this->platform->defaultPrimaryKeyToNull() && $table->hasPrimaryKeyColumn()
                ? $table->getPrimaryKeyColumnName() : null;

            if ($rowsData) {
                $insert = $this->prepare($this->platform->compilePreparedInsert($table, $this->platform->defaultPrimaryKeyToNull()));

                foreach ($rowsData as $key => $row) {
                    $params = $row + $defaultData;

                    if ($primaryKeyToRemove) {
                        unset($params[$primaryKeyToRemove]);
                    }

                    $insert->setParameters($params);
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

            $rowsData       = $this->platform->mapResultSetToDbFormat($rows);
            $groupedRowData = $this->groupRowDataBySuppliedColumns($rowsData);

            foreach ($groupedRowData as $rows) {
                $columnsToUpdate = array_keys($rows[0]);
                $update          = $this->createPreparedUpdatedWithWhereId($table, $columnsToUpdate);

                foreach ($rows as $key => $row) {
                    $update->setParameters($row);
                    $update->execute();

                    if ($update->getAffectedRows() !== 1) {
                        throw new DbOutOfSyncException(
                            $rowArray[$key],
                            null // There is no row with the supplied id.
                        );
                    }
                }
            }
        });
    }

    protected function createPreparedUpdatedWithWhereId(Table $table, array $columnsToUpdate, array $lockingColumnNameParameterMap = [])
    {
        $primaryKey = $table->getPrimaryKeyColumnName();

        return $this->prepare($this->platform->compilePreparedUpdate(
            $table,
            array_diff($columnsToUpdate, [$primaryKey]),
            $lockingColumnNameParameterMap + [$primaryKey => $primaryKey]
        ));
    }

    /**
     * @param array[] $rows
     *
     * @return array[][]
     */
    protected function groupRowDataBySuppliedColumns(array $rows) : array
    {
        $groups = [];

        foreach ($rows as $row) {
            $columnNames = array_keys($row);
            sort($columnNames, SORT_STRING);
            $key = implode('||', $columnNames);

            $groups[$key][] = $row;
        }

        return $groups;
    }

    /**
     * @inheritDoc
     */
    public function resequenceOrderIndexColumn(ResequenceOrderIndexColumn $query)
    {
        $compiled = $this->loadQueryFrom($this->platform->compileResequenceOrderIndexColumn($query));
        $compiled->execute();
    }
}