<?php declare(strict_types = 1);

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
    public static function fromRowArray(Table $table, array $rows) : RowSet
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
    public function createRow(array $data) : Row
    {
        return new Row($this->table, $data);
    }

    /**
     * @param array $rows
     *
     * @return self
     */
    public function withRows(array $rows) : self
    {
        return new self($this->table, $rows);
    }

    /**
     * @return Table
     */
    public function getTable() : Schema\Table
    {
        return $this->table;
    }

    /**
     * @return array
     */
    public function getPrimaryKeys() : array
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
    public function getRowsWithoutPrimaryKeys() : RowSet
    {
        if (!$this->primaryKey) {
            return $this;
        }

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
    public function getRowsWithPrimaryKeys() : RowSet
    {
        if (!$this->primaryKey) {
            return $this->withRows([]);
        }

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
    public function getRows() : array
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
    public function asArray() : array
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
    public function has(int $primaryKey) : bool
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
    public function add(Row $row) : bool
    {
        if ($row->getTable()->getName() !== $this->table->getName()) {
            throw InvalidArgumentException::format(
                    'The supplied row does not match the table: expecting %s, %s given',
                    $this->table->getName(),
                    $row->getTable()->getName()
            );
        }

        if ($this->primaryKey && $row->hasColumn($this->primaryKey) && $this->has($row->getColumn($this->primaryKey))) {
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
    public function remove(int $primaryKey) : bool
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