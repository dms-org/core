<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Schema;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Util\Debug;

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
    public function __construct(string $name, array $columns, array $indexes = [], array $foreignKeys = [])
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

            if ($foreignKey->requiresNullableColumns()) {
                foreach ($foreignKey->getLocalColumnNames() as $column) {
                    if (!$this->getColumn($column)->getType()->isNullable()) {
                        throw InvalidArgumentException::format(
                            'Invalid foreign key \'%s\' supplied to table \'%s\': foreign key has a ON DELETE|UPDATE SET NULL condition and hence the columns must be nullable, \'%s\' is not nullable',
                            $foreignKey->getName(), $this->name, $column
                        );
                    }
                }
            }

            $this->foreignKeys[$foreignKey->getName()] = $foreignKey;
        }
    }

    private function verifyColumns($itemName, array $columnNames)
    {
        foreach ($columnNames as $columnName) {
            if (!$this->hasColumn($columnName)) {
                throw InvalidArgumentException::format(
                    'Invalid column name in %s: expecting one of (%s), \'%s\' given',
                    $itemName, Debug::formatValues($this->getColumnNames()), $columnName
                );
            }
        }
    }

    /**
     * @return string
     */
    public function getName() : string
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
    public function hasPrimaryKeyColumn() : bool
    {
        return $this->primaryKeyColumn !== null;
    }

    /**
     * @return Column[]
     */
    public function getColumns() : array
    {
        return $this->columns;
    }

    /**
     * @return string[]
     */
    public function getColumnNames() : array
    {
        return array_keys($this->columns);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasColumn(string $name) : bool
    {
        return isset($this->columns[$name]);
    }

    /**
     * @param string $name
     *
     * @return Column
     * @throws InvalidArgumentException
     */
    public function getColumn(string $name) : Column
    {
        $column = $this->findColumn($name);

        if (!$column) {
            throw InvalidArgumentException::format(
                'Could not get column from table \'%s\': expecting one of (%s), \'%s\' given',
                $this->name, Debug::formatValues($this->getColumnNames()), $name
            );
        }

        return $column;
    }

    /**
     * @param string $name
     *
     * @return Column|null
     * @throws InvalidArgumentException
     */
    public function findColumn(string $name)
    {
        if (!is_string($name)) {
            throw InvalidArgumentException::format('Column name must be string, %s given', gettype($name));
        }

        return isset($this->columns[$name]) ? $this->columns[$name] : null;
    }

    /**
     * @return null[]
     */
    public function getNullColumnData() : array
    {
        return $this->nullColumnData;
    }

    /**
     * @return Index[]
     */
    public function getIndexes() : array
    {
        return $this->indexes;
    }

    /**
     * @return ForeignKey[]
     */
    public function getForeignKeys() : array
    {
        return $this->foreignKeys;
    }

    /**
     * @param string $name
     *
     * @return Table
     */
    public function withName(string $name) : Table
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
    public function withColumns(array $columns) : Table
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
    public function withColumnsButIgnoringConstraints(array $columns) : Table
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
    public function withPrefix(string $prefix) : Table
    {
        return $this
            ->withColumnsPrefixedBy($prefix)
            ->withNameAndConstraintsPrefixedBy($prefix);
    }

    /**
     * Returns a table with the name and constraints prefixed.
     *
     * @param string $prefix
     *
     * @return Table
     */
    public function withNameAndConstraintsPrefixedBy(string $prefix) : Table
    {
        if ($prefix === '') {
            return $this;
        }

        $indexes = [];
        foreach ($this->indexes as $index) {
            $indexes[] = $index->withNamePrefixedBy($prefix);
        }

        $foreignKeys = [];
        foreach ($this->foreignKeys as $foreignKey) {
            $foreignKeys[] = $foreignKey->withNamePrefixedBy($prefix);
        }

        return new self(
            $prefix . $this->name,
            $this->columns,
            $indexes,
            $foreignKeys
        );
    }

    /**
     * Returns a table with the name and columns prefixed.
     *
     * @param string $prefix
     *
     * @return Table
     */
    public function withColumnsPrefixedBy(string $prefix) : Table
    {
        $columns = [];
        foreach ($this->columns as $column) {
            $columns[] = $column->withPrefix($prefix);
        }

        $indexes = [];
        foreach ($this->indexes as $index) {
            $indexes[] = $index->withColumnsPrefixedBy($prefix);
        }

        $foreignKeys = [];
        foreach ($this->foreignKeys as $foreignKey) {
            $foreignKeys[] = $foreignKey->withLocalColumnsPrefixedBy($prefix);
        }


        return new self(
            $this->name,
            $columns,
            $indexes,
            $foreignKeys
        );
    }

    /**
     * @param Index[] $indexes
     *
     * @return Table
     */
    public function withIndexes(array $indexes) : Table
    {
        return new self($this->name, $this->columns, $indexes, $this->foreignKeys);
    }

    /**
     * @param ForeignKey[] $foreignKeys
     *
     * @return Table
     */
    public function withForeignKeys(array $foreignKeys) : Table
    {
        return new self($this->name, $this->columns, $this->indexes, $foreignKeys);
    }
}