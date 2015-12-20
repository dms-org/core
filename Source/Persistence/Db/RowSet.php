<?php

namespace Dms\Core\Persistence\Db;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Schema\Table;

/**
 * The row set class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RowSet
{
    /**
     * @var Table
     */
    private $table;

    /**
     * @var Row[]
     */
    private $rows = [];

    /**
     * RowSet constructor.
     *
     * @param Table $table
     * @param Row[] $rows
     *
     * @throws InvalidArgumentException
     */
    public function __construct(Table $table, array $rows = [])
    {
        $this->table      = $table;
        $this->primaryKey = $table->getPrimaryKeyColumnName();

        foreach ($rows as $row) {
            $this->add($row);
        }
    }

    /**
     * @param Table $table
     * @param array $rows
     *
     * @return RowSet
     */
    public static function fromRowArray(Table $table, array $rows)
    {
        $self = new self($table);

        foreach ($rows as $row) {
            $self->rows[] = $self->createRow($row);
        }

        return $self;
    }

    /**
     * @param array $data
     *
     * @return Row
     */
    public function createRow(array $data)
    {
        return new Row($this->table, $data);
    }

    /**
     * @param array $rows
     *
     * @return string
     */
    public function withRows(array $rows)
    {
        return new self($this->table, $rows);
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return array
     */
    public function getPrimaryKeys()
    {
        $primaryKeys = [];

        foreach ($this->rows as $row) {
            $primaryKey = $row->getColumn($this->primaryKey);
            if ($primaryKey) {
                $primaryKeys[] = $primaryKey;
            }
        }

        return $primaryKeys;
    }

    /**
     * @return RowSet
     */
    public function getRowsWithoutPrimaryKeys()
    {
        $rowWithoutPrimaryKeys = [];

        foreach ($this->rows as $row) {
            if (!$row->hasColumn($this->primaryKey)) {
                $rowWithoutPrimaryKeys[] = $row;
            }
        }

        return $this->withRows($rowWithoutPrimaryKeys);
    }

    /**
     * @return RowSet
     */
    public function getRowsWithPrimaryKeys()
    {
        $rowWithPrimaryKeys = [];

        foreach ($this->rows as $row) {
            if ($row->hasColumn($this->primaryKey)) {
                $rowWithPrimaryKeys[] = $row;
            }
        }

        return $this->withRows($rowWithPrimaryKeys);
    }

    /**
     * @return Row[]
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @return Row|null
     */
    public function getFirstRowOrNull()
    {
        return reset($this->rows) ?: null;
    }

    /**
     * @return array[]
     */
    public function asArray()
    {
        $columnData = [];

        foreach ($this->rows as $row) {
            $columnData[] = $row->getColumnData();
        }

        return $columnData;
    }

    /**
     * @param int $primaryKey
     *
     * @return bool
     */
    public function has($primaryKey)
    {
        foreach ($this->rows as $row) {
            if ($row->getColumn($this->primaryKey) === $primaryKey) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Row $row
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function add(Row $row)
    {
        if ($row->getTable()->getName() !== $this->table->getName()) {
            throw InvalidArgumentException::format(
                    'The supplied row does not match the table: expecting %s, %s given',
                    $this->table->getName(),
                    $row->getTable()->getName()
            );
        }

        if ($row->hasColumn($this->primaryKey) && $this->has($row->getColumn($this->primaryKey))) {
            return false;
        }

        $this->rows[] = $row;

        return true;
    }

    /**
     * @param int $primaryKey
     *
     * @return bool
     */
    public function remove($primaryKey)
    {
        foreach ($this->rows as $key => $row) {
            if ($row->getColumn($this->primaryKey) === $primaryKey) {
                unset($this->rows[$key]);
                return true;
            }
        }

        return false;
    }

    public function count()
    {
        return count($this->rows);
    }
}