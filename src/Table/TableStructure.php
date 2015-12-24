<?php

namespace Dms\Core\Table;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Util\Debug;

/**
 * The table structure class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableStructure implements ITableStructure
{
    /**
     * @var IColumn[]
     */
    protected $columns;

    /**
     * TableStructure constructor.
     *
     * @param IColumn[] $columns
     */
    public function __construct(array $columns)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'columns', $columns, IColumn::class);

        foreach ($columns as $column) {
            $this->columns[$column->getName()] = $column;
        }
    }

    /**
     * @return IColumn[]
     */
    final public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return string[]
     */
    final public function getColumnNames()
    {
        return array_keys($this->columns);
    }

    final public function hasColumn($name)
    {
        return isset($this->columns[$name]);
    }

    /**
     * @inheritdoc
     */
    final public function getColumn($name)
    {
        if (!isset($this->columns[$name])) {
            throw InvalidArgumentException::format(
                    'Invalid column name: expecting one of (%s), %s given',
                    Debug::formatValues(array_keys($this->columns)), $name
            );
        }

        return $this->columns[$name];
    }

    /**
     * @inheritdoc
     */
    final public function getColumnAndComponent($componentId)
    {
        if (strpos($componentId, '.') === false) {
            $columnName    = $componentId;
            $componentName = null;
        } else {
            list($columnName, $componentName) = explode('.', $componentId);
        }

        $column = $this->getColumn($columnName);

        return [$column, $column->getComponent($componentName)];
    }

    /**
     * @inheritdoc
     */
    final public function getComponent($componentId)
    {
        return $this->getColumnAndComponent($componentId)[1];
    }

    /**
     * @inheritDoc
     */
    public function withColumns(array $columns)
    {
        return new self($columns);
    }
}