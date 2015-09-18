<?php

namespace Iddigital\Cms\Core\Persistence\Db\Connection;

use Iddigital\Cms\Core\Persistence\Db\Platform\CompiledQuery;
use Iddigital\Cms\Core\Persistence\Db\Platform\IPlatform;
use Iddigital\Cms\Core\Persistence\Db\Query\BulkUpdate;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Query\Update;
use Iddigital\Cms\Core\Persistence\Db\Query\Upsert;

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
        if($this->isInTransaction()) {
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
            $rows  = $query->getRows();
            $table = $rows->getTable();

            $rowsWithoutKeys = $rows->getRowsWithoutPrimaryKeys();
            $rowArray        = $rowsWithoutKeys->getRows();
            $rowsData        = $this->platform->mapResultSetToDbFormat($rowsWithoutKeys);
            $insert          = $this->prepare($this->platform->compilePreparedInsert($table));
            if ($rowsData) {

                foreach ($rowsData as $key => $row) {
                    $insert->setParameters($row);
                    $insert->execute();
                    $rowArray[$key]->firePrimaryKeyCallbacks($this->getLastInsertId());
                }
            }

            $rowsWithKeys = $rows->getRowsWithPrimaryKeys();
            $rowsData     = $this->platform->mapResultSetToDbFormat($rowsWithKeys);
            if ($rowsData) {
                $update = $this->prepare($this->platform->compilePreparedUpdate(
                        $table,
                        [$table->getPrimaryKeyColumnName()]
                ));

                foreach ($rowsData as $key => $row) {
                    $update->setParameters($row);
                    $update->execute();

                    if ($update->getAffectedRows() !== 1) {
                        $insert->setParameters($row);
                        $insert->execute();
                    }
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
            $rows  = $query->getRows();
            $table = $rows->getTable();

            $rowsData = $this->platform->mapResultSetToDbFormat($rows);

            $update = $this->prepare($this->platform->compilePreparedUpdate(
                    $table,
                    [$table->getPrimaryKeyColumnName()]
            ));

            foreach ($rowsData as $key => $row) {
                $update->setParameters($row);
                $update->execute();

                if ($update->getAffectedRows() !== 1) {
                    // TODO: throw lock exception
                }
            }
        });
    }

}