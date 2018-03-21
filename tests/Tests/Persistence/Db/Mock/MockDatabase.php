<?php

namespace Dms\Core\Tests\Persistence\Db\Mock;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\Table;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MockDatabase
{
    /**
     * @var string
     */
    private $transactionStack = [];

    /**
     * @var MockTable[]
     */
    private $tables = [];

    /**
     * @var bool
     */
    private $checkForeignKeys = true;

    /**
     * @var int|null
     */
    private $lastInsertId;

    public function beginTransaction()
    {
        $this->transactionStack[] = serialize($this->tables);
    }

    public function isInTransaction()
    {
        return !empty($this->transactionStack);
    }

    public function rollbackTransaction()
    {
        if ($this->isInTransaction()) {
            $this->tables = unserialize(array_pop($this->transactionStack));
            foreach ($this->tables as $table) {
                $table->setDb($this);
            }

            return true;
        }

        return false;
    }

    public function commitTransaction()
    {
        if ($this->isInTransaction()) {
            array_pop($this->transactionStack);

            return true;
        }

        return false;
    }

    /**
     * @param callable $operation
     *
     * @return mixed
     * @throws \Exception
     */
    public function withinTransaction(callable $operation)
    {
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

    protected function impliedTransaction(callable $operation)
    {
        if ($this->isInTransaction()) {
            return $operation();
        } else {
            return $this->withinTransaction(function () use ($operation) {
                return $operation();
            });
        }
    }

    /**
     * @param Table $structure
     *
     * @return MockTable
     */
    public function createTable(Table $structure)
    {
        $mockTable = new MockTable($structure);
        $mockTable->setDb($this);
        $this->tables[$structure->getName()] = $mockTable;

        return $mockTable;
    }

    /**
     * @return void
     * @throws \Dms\Core\Exception\InvalidOperationException
     */
    public function loadForeignKeys()
    {
        foreach ($this->tables as $table) {
            $table->loadForeignKeys($this);
        }

        $this->validateConstraints();
    }

    /**
     * @return void
     */
    public function validateConstraints()
    {
        if (!$this->checkForeignKeys) {
            return;
        }

        foreach ($this->tables as $table) {
            $table->validateConstraints();
        }
    }

    /**
     * Gets an array of all the table data indexed by
     * the table name.
     *
     * @return array[][]
     */
    public function getData()
    {
        $rowSets = [];

        foreach ($this->tables as $table) {
            $rowSets[$table->getName()] = $table->getRows();
        }

        return $rowSets;
    }

    public function setData(array $tableRowSetsMap)
    {
        return $this->impliedTransaction(function () use ($tableRowSetsMap) {
            foreach ($tableRowSetsMap as $table => $rowSet) {
                if (!$this->hasTable($table)) {
                    throw InvalidArgumentException::format('Unknown table %s', $table);
                }

                $this->getTable($table)->setRows($rowSet);
            }

            $this->validateConstraints();
        });
    }

    public function query(PhpCompiledQuery $query)
    {
        return $this->impliedTransaction(function () use ($query) {
            $results = $query->executeOn($this);

                $this->validateConstraints();

            return $results;
        });
    }

    /**
     * @return MockTable[]
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * @param string $table
     *
     * @return MockTable|null
     */
    public function getTable($table)
    {
        return $this->hasTable($table) ? $this->tables[$table] : null;
    }

    /**
     * @param string $table
     *
     * @return bool
     */
    public function hasTable($table)
    {
        return isset($this->tables[$table]);
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function hasColumn($identifier)
    {
        return $this->getColumn($identifier) !== null;
    }

    /**
     * @param string $identifier
     *
     * @return Column|null
     */
    public function getColumn($identifier)
    {
        $tableAndColumn = $this->getTableAndColumn($identifier);

        return $tableAndColumn ? $tableAndColumn[1] : null;
    }

    /**
     * @param string $identifier
     *
     * @return array
     */
    private function getTableAndColumn($identifier)
    {
        list($tableName, $columnName) = explode('.', $identifier);

        if (!isset($this->tables[$tableName])) {
            return null;
        }

        $table = $this->tables[$tableName];

        return $table->getStructure()->hasColumn($columnName)
                ? [$table, $table->getStructure()->findColumn($columnName)]
                : null;
    }

    public function createForeignKey($mainColumnIdentifier, $referencedColumnIdentifier, string $deleteMode = ForeignKeyMode::CASCADE)
    {
        /** @var MockTable $mainTable */
        list($mainTable, $mainColumn) = $this->getTableAndColumn($mainColumnIdentifier);
        list($referencedTable, $referencedColumn) = $this->getTableAndColumn($referencedColumnIdentifier);

        if (!$mainTable) {
            throw InvalidArgumentException::format('Cannot create foreign key: invalid identifier %s', $mainColumnIdentifier);
        }

        if (!$referencedTable) {
            throw InvalidArgumentException::format('Cannot create foreign key: invalid identifier %s', $referencedColumnIdentifier);
        }

        $this->impliedTransaction(function () use ($mainTable, $mainColumn, $referencedTable, $referencedColumn, $deleteMode) {
            $mainTable->addForeignKey($mainColumn, $referencedTable, $referencedColumn, $deleteMode);
        });
    }

    public function disableForeignKeyChecks()
    {
        $this->checkForeignKeys = false;
    }

    public function getLastInsertId()
    {
        return $this->lastInsertId;
    }

    public function setLastInsertId($id)
    {
        $this->lastInsertId = $id;
    }
}