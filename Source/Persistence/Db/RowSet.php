<?php

namespace Iddigital\Cms\Core\Persistence\Db;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

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
     * @var Row[]
     */
    private $rowsWithoutPrimaryKeys = [];

    /**
     * RowSet constructor.
     *
     * @param Table      $table
     * @param Row[]       $rows
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
    public static function fromRowArray(Table $table, $rows)
    {
        $self = new self($table);

        foreach ($rows as $row) {
            if (isset($row[$self->primaryKey])) {
                $self->rows[$row[$self->primaryKey]] = $self->createRow($row);
            } else {
                $self->rowsWithoutPrimaryKeys[] = $self->createRow($row);
            }
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
        return array_keys($this->rows);
    }

    /**
     * @return RowSet
     */
    public function getRowsWithoutPrimaryKeys()
    {
        return $this->withRows($this->rowsWithoutPrimaryKeys);
    }

    /**
     * @return RowSet
     */
    public function getRowsWithPrimaryKeys()
    {
        return $this->withRows($this->rows);
    }

    /**
     * @return Row[]
     */
    public function getRows()
    {
        return array_merge($this->rows, $this->rowsWithoutPrimaryKeys);
    }

    /**
     * @return array[]
     */
    public function asArray()
    {
        $columnData = [];

        foreach ($this->getRows() as $row) {
            $columnData[] = $row->getColumnData();
        }

        return $columnData;
    }

    /**
     * @param $primaryKey
     *
     * @return bool
     */
    public function has($primaryKey)
    {
        return isset($this->rows[$primaryKey]);
    }

    /**
     * @param Row $row
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function add(Row $row)
    {
        if ($row->getTable() !== $this->table) {
            throw InvalidArgumentException::format(
                    'The supplied row does not match the table: expecting %s, %s given',
                    $this->table->getName(),
                    $row->getTable()->getName()
            );
        }

        if (!$row->hasPrimaryKey()) {
            $this->rowsWithoutPrimaryKeys[] = $row;

            return true;
        }

        $primaryKey = $row->getPrimaryKey();

        if ($this->has($primaryKey)) {
            return false;
        }

        $this->rows[$primaryKey] = $row;

        return true;
    }

    /**
     * @param int $primaryKey
     *
     * @return bool
     */
    public function remove($primaryKey)
    {
        if ($this->has($primaryKey)) {
            unset($this->rows[$primaryKey]);

            return true;
        }

        return false;
    }

    public function count()
    {
        return count($this->rows) + count($this->rowsWithoutPrimaryKeys);
    }
}