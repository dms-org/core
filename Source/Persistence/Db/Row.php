<?php

namespace Iddigital\Cms\Core\Persistence\Db;

use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The row class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Row
{
    /**
     * @var Table
     */
    private $table;

    /**
     * @var array
     */
    private $columnData = [];

    /**
     * @var callable
     */
    private $onInsertCallbacks = [];

    /**
     * @var string|null
     */
    private $primaryKey;

    /**
     * @param Table $table
     * @param array $columnData
     */
    public function __construct(Table $table, array $columnData = [])
    {
        $this->table      = $table;
        $this->primaryKey = $table->getPrimaryKeyColumnName();
        $this->columnData = $columnData + $table->getNullColumnData();
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyColumn()
    {
        return $this->primaryKey;
    }

    /**
     * @return bool
     */
    public function hasPrimaryKey()
    {
        return $this->primaryKey && $this->columnData[$this->primaryKey] !== null;
    }

    /**
     * @return int|null
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey ? $this->columnData[$this->primaryKey] : null;
    }

    /**
     * @param int|null $value
     *
     * @throws InvalidOperationException
     */
    public function setPrimaryKey($value)
    {
        if (!$this->primaryKey) {
            throw InvalidOperationException::methodCall(__METHOD__, 'Table does not have a primary key');
        }

        $this->columnData[$this->primaryKey] = $value;
    }

    /**
     * @return array
     */
    public function getColumnData()
    {
        return $this->columnData;
    }

    /**
     * Returns whether the column exists and it is not null.
     *
     * @param string $column
     *
     * @return mixed
     */
    public function hasColumn($column)
    {
        return isset($this->columnData[$column]);
    }

    /**
     * @param string $column
     *
     * @return mixed
     */
    public function getColumn($column)
    {
        return isset($this->columnData[$column]) ? $this->columnData[$column] : null;
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return void
     */
    public function setColumn($column, $value)
    {
        $this->columnData[$column] = $value;
    }

    /**
     * @param callable $callback
     *
     * @return void
     */
    public function onInsertPrimaryKey(callable $callback)
    {
        $this->onInsertCallbacks[] = $callback;
    }

    /**
     * @param int $primaryKey
     *
     * @return void
     */
    public function firePrimaryKeyCallbacks($primaryKey)
    {
        foreach ($this->onInsertCallbacks as $callback) {
            $callback($primaryKey);
        }
    }
}