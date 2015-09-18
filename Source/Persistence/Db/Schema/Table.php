<?php

namespace Iddigital\Cms\Core\Persistence\Db\Schema;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;

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
     * Table constructor.
     *
     * @param string   $name
     * @param Column[] $columns
     *
     * @throws InvalidArgumentException
     */
    public function __construct($name, array $columns)
    {
        InvalidArgumentException::verify(is_string($name), 'Table name must be a string, %s given', gettype($name));
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'columns', $columns, Column::class);

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
     * @param string $name
     *
     * @return Table
     */
    public function withName($name)
    {
        if ($this->name === $name) {
            return $this;
        }

        return new self($name, $this->columns);
    }

    /**
     * @param Column[] $columns
     *
     * @return Table
     */
    public function withColumns(array $columns)
    {
        if ($this->columns === $columns) {
            return $this;
        }

        return new self($this->name, $columns);
    }
}