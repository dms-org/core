<?php

namespace Iddigital\Cms\Core\Persistence\Db\Schema;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The table class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Table
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Column|null
     */
    private $primaryKeyColumn;

    /**
     * @var Column[]
     */
    private $columns = [];

    /**
     * @var null[]
     */
    private $nullColumnData = [];

    /**
     * @var Index[]
     */
    private $indexes = [];

    /**
     * @var ForeignKey[]
     */
    private $foreignKeys = [];

    /**
     * Table constructor.
     *
     * @param string       $name
     * @param Column[]     $columns
     * @param Index[]      $indexes
     * @param ForeignKey[] $foreignKeys
     *
     * @throws InvalidArgumentException
     */
    public function __construct($name, array $columns, array $indexes = [], array $foreignKeys = [])
    {
        InvalidArgumentException::verify(is_string($name), 'Table name must be a string, %s given', gettype($name));
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'columns', $columns, Column::class);
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'indexes', $indexes, Index::class);
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'foreignKeys', $foreignKeys, ForeignKey::class);

        $this->name = $name;

        foreach ($columns as $column) {
            if ($column->isPrimaryKey()) {
                if ($this->primaryKeyColumn) {
                    throw InvalidArgumentException::format(
                            'Invalid columns supplied to table: duplicate primary key columns \'%s\' and \'%s\'',
                            $this->primaryKeyColumn->getName(),
                            $column->getName()
                    );
                }

                $this->primaryKeyColumn = $column;
            }

            $this->columns[$column->getName()] = $column;
        }

        if ($this->hasPrimaryKeyColumn()) {
            $this->nullColumnData[$this->getPrimaryKeyColumnName()] = null;
        }

        foreach ($this->getColumnNames() as $name) {
            $this->nullColumnData[$name] = null;
        }

        foreach ($indexes as $index) {
            $this->verifyColumns('index ' . $index->getName(), $index->getColumnNames());
            $this->indexes[$index->getName()] = $index;
        }

        foreach ($foreignKeys as $foreignKey) {
            $this->verifyColumns('foreign key ' . $foreignKey->getName(), $foreignKey->getLocalColumnNames());
            $this->foreignKeys[$foreignKey->getName()] = $foreignKey;
        }
    }

    private function verifyColumns($itemName, array $columnNames)
    {
        foreach ($columnNames as $columnName) {
            if (!$this->hasColumn($columnName)) {
                throw InvalidArgumentException::format(
                        'Invalid column name in %s: expecting one of (%s), %s given',
                        $itemName, Debug::formatValues($this->getColumnNames()), $columnName
                );
            }
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Column|null
     */
    public function getPrimaryKeyColumn()
    {
        return $this->primaryKeyColumn;
    }

    /**
     * @return string|null
     */
    public function getPrimaryKeyColumnName()
    {
        return $this->primaryKeyColumn ? $this->primaryKeyColumn->getName() : null;
    }

    /**
     * @return bool
     */
    public function hasPrimaryKeyColumn()
    {
        return $this->primaryKeyColumn !== null;
    }

    /**
     * @return Column[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return string[]
     */
    public function getColumnNames()
    {
        return array_keys($this->columns);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasColumn($name)
    {
        return isset($this->columns[$name]);
    }

    /**
     * @param string $name
     *
     * @return Column|null
     */
    public function getColumn($name)
    {
        InvalidArgumentException::verify(is_string($name), 'Column name must be string, %s given', gettype($name));

        return isset($this->columns[$name]) ? $this->columns[$name] : null;
    }

    /**
     * @return null[]
     */
    public function getNullColumnData()
    {
        return $this->nullColumnData;
    }

    /**
     * @return Index[]
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * @return ForeignKey[]
     */
    public function getForeignKeys()
    {
        return $this->foreignKeys;
    }

    /**
     * @param string $name
     *
     * @return Table
     */
    public function withName($name)
    {
        if ($this->name === $name) {
            return $this;
        }

        return new self($name, $this->columns, $this->indexes, $this->foreignKeys);
    }

    /**
     * Returns the table with the supplied columns.
     *
     * @param Column[] $columns
     *
     * @return Table
     */
    public function withColumns(array $columns)
    {
        if ($this->columns === $columns) {
            return $this;
        }

        return new self($this->name, $columns, $this->indexes, $this->foreignKeys);
    }

    /**
     * Returns the table with the supplied columns.
     * NOTE: indexes and foreign keys are not kept.
     *
     * @param Column[] $columns
     *
     * @return Table
     */
    public function withColumnsIgnoringConstraints(array $columns)
    {
        if ($this->columns === $columns) {
            return $this;
        }

        return new self($this->name, $columns);
    }

    /**
     * Returns a table with the name and columns prefixed.
     *
     * @param string $prefix
     *
     * @return Table
     */
    public function withPrefix($prefix)
    {
        $columns = [];
        foreach ($this->columns as $column) {
            $columns[] = $column->withPrefix($prefix);
        }

        $indexes = [];
        foreach ($this->indexes as $index) {
            $indexes[] = $index->withPrefix($prefix);
        }

        $foreignKeys = [];
        foreach ($this->foreignKeys as $foreignKey) {
            $foreignKeys[] = $foreignKey->withPrefix($prefix);
        }

        return new self(
                $prefix . $this->name,
                $columns,
                $indexes,
                $foreignKeys
        );
    }

    /**
     * @param ForeignKey[] $foreignKeys
     *
     * @return Table
     */
    public function withForeignKeys(array $foreignKeys)
    {
        return new self($this->name, $this->columns, $this->indexes, $foreignKeys);
    }
}