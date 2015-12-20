<?php

namespace Dms\Core\Tests\Persistence\Db\Mock;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Table;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MockTable implements \Serializable
{
    /**
     * @var MockDatabase|null
     */
    private $db;

    /**
     * @var Table
     */
    private $structure;

    /**
     * @var bool
     */
    private $hasLoadedForeignKeys = false;

    /**
     * @var string|null
     */
    private $primaryKey;

    /**
     * @var MockForeignKey[]
     */
    private $foreignKeys = [];

    /**
     * @var array[]
     */
    private $rows = [];

    /**
     * @var int
     */
    private $nextPrimaryKey = 1;

    /**
     * MockTable constructor.
     *
     * @param Table $structure
     */
    public function __construct(Table $structure)
    {
        $this->structure  = $structure;
        $this->primaryKey = $structure->getPrimaryKeyColumnName();
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        $originalDb = $this->db;
        $this->db   = null;
        $serialized = serialize($this);
        $this->db   = $originalDb;

        return $serialized;
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {

    }

    public function setDb(MockDatabase $db)
    {
        $this->db = $db;
    }

    /**
     * @param MockDatabase $database
     *
     * @return void
     * @throws InvalidOperationException
     * @throws InvalidArgumentException
     */
    public function loadForeignKeys(MockDatabase $database)
    {
        if ($this->hasLoadedForeignKeys === true) {
            return;
        }

        foreach ($this->structure->getForeignKeys() as $fk) {
            if (count($fk->getLocalColumnNames()) !== 1) {
                throw InvalidOperationException::format('Mock database does not support foreign keys with multiple columns');
            }

            $referencedTable = $database->getTable($fk->getReferencedTableName());

            if (!$referencedTable) {
                throw InvalidArgumentException::format(
                        'Unknown referenced table %s', $fk->getReferencedTableName()
                );
            }

            $referencedColumn = $referencedTable->getStructure()->findColumn($fk->getReferencedColumnNames()[0]);

            if (!$referencedColumn) {
                throw InvalidArgumentException::format(
                        'Unknown referenced column %s.%s', $referencedTable->getName(), $fk->getReferencedColumnNames()[0]
                );
            }

            $this->foreignKeys[] = new MockForeignKey(
                    $this,
                    $this->structure->findColumn($fk->getLocalColumnNames()[0]),
                    $referencedTable,
                    $referencedColumn
            );
        }

        $this->hasLoadedForeignKeys = true;
    }

    /**
     * @param Column    $column
     * @param MockTable $referencedTable
     * @param Column    $referencedColumn
     *
     * @return void
     * @throws ForeignKeyConstraintException
     */
    public function addForeignKey(Column $column, MockTable $referencedTable, Column $referencedColumn)
    {
        $foreignKey = new MockForeignKey($this, $column, $referencedTable, $referencedColumn);
        $foreignKey->validate();
        $this->foreignKeys[] = $foreignKey;
    }

    /**
     * @return Table
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * @return MockForeignKey[]
     */
    public function getForeignKeys()
    {
        return $this->foreignKeys;
    }

    public function getName()
    {
        return $this->getStructure()->getName();
    }

    /**
     * @return array[]
     */
    public function getRows()
    {
        return $this->rows;
    }

    public function getColumnData($name)
    {
        if (!$this->structure->hasColumn($name)) {
            return null;
        }

        return array_column($this->rows, $name);
    }

    public function validateConstraints()
    {
        foreach ($this->foreignKeys as $foreignKey) {
            $foreignKey->validate();
        }
    }

    public function truncate()
    {
        $this->rows = [];
    }

    public function setRows(array $rows)
    {
        $this->truncate();
        $this->bulkInsert($rows);
    }

    public function bulkInsert(array $rows)
    {
        foreach ($rows as $row) {
            $this->insert($row);
        }
    }

    /**
     * Returns whether the supplied row has a primary key.
     *
     * @param int $primaryKey
     *
     * @return bool
     */
    public function hasRowWithPrimaryKey($primaryKey)
    {
        if (!$this->primaryKey) {
            return false;
        }

        return isset($this->rows[$primaryKey]);
    }

    /**
     * Inserts the row returning the primary key.
     *
     * @param array $row
     *
     * @return int
     * @throws DuplicateKeyException
     * @throws InvalidArgumentException
     */
    public function insert(array $row)
    {
        $row = $this->validateRowFormat($row);

        if ($this->primaryKey !== null) {
            $key =& $row[$this->primaryKey];

            if ($key === null) {
                $key = $this->nextPrimaryKey++;
            } elseif (isset($this->rows[$key])) {
                throw new DuplicateKeyException($this->structure, $this->structure->getPrimaryKeyColumn());
            } elseif ($key >= $this->nextPrimaryKey) {
                $this->nextPrimaryKey = $key + 1;
            }

            $this->rows[$key] = $row;

            if ($this->db) {
                $this->db->setLastInsertId($key);
            }
        } else {
            $key          = null;
            $this->rows[] = $row;
        }


        return $key;
    }

    /**
     * Updates the row with the supplied primary key.
     *
     * @param int   $primaryKey
     * @param array $row
     *
     * @return bool
     * @throws InvalidArgumentException
     * @throws InvalidOperationException
     */
    public function update($primaryKey, array $row)
    {
        if (!$this->primaryKey) {
            throw InvalidOperationException::methodCall(__METHOD__, 'table does not have a primary key');
        }

        $row = $this->validateRowFormat($row);

        if ($this->hasRowWithPrimaryKey($primaryKey)) {
            $this->rows[$primaryKey] = $row;

            return true;
        } else {
            return false;
        }
    }

    /**
     * Updates the columns in the row with the supplied primary key.
     *
     * @param int   $primaryKey
     * @param array $columnData
     *
     * @return bool
     * @throws InvalidArgumentException
     * @throws InvalidOperationException
     */
    public function updateColumns($primaryKey, array $columnData)
    {
        if (!$this->primaryKey) {
            throw InvalidOperationException::methodCall(__METHOD__, 'table does not have a primary key');
        }

        $this->validateRowFormat($columnData + $this->structure->getNullColumnData());

        if ($this->hasRowWithPrimaryKey($primaryKey)) {
            foreach ($columnData as $column => $value) {
                $this->rows[$primaryKey][$column] = $value;
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $row
     *
     * @return array
     * @throws InvalidArgumentException
     */
    protected function validateRowFormat(array $row)
    {
        $expected = $this->structure->getColumns();
        if (array_diff_key($row, $expected) || array_diff_key($expected, $row)) {
            throw InvalidArgumentException::format(
                    'Invalid row: expecting columns [%s], [%s] given',
                    implode(', ', $this->structure->getColumnNames()),
                    implode(', ', array_keys($row))
            );
        }

        // Ensure correct order
        return array_merge($expected, $row);
    }
}