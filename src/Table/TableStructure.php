<?php declare(strict_types = 1);

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
    final public function getColumns() : array
    {
        return $this->columns;
    }

    /**
     * @return string[]
     */
    final public function getColumnNames() : array
    {
        return array_keys($this->columns);
    }

    final public function hasColumn($name) : bool
    {
        return isset($this->columns[$name]);
    }

    /**
     * @inheritdoc
     */
    final public function getColumn(string $name) : IColumn
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
     * @inheritDoc
     */
    final public function normalizeComponentId(string $componentId) : string
    {
        /** @var IColumn $column */
        /** @var IColumnComponent $column */
        list($column, $component) = $this->getColumnAndComponent($componentId);

        return $column->getName() . '.' . $component->getName();
    }

    /**
     * @inheritdoc
     */
    final public function getColumnAndComponent(string $componentId) : array
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
     * @inheritDoc
     */
    final public function hasComponent(string $componentId) : bool
    {
        if (strpos($componentId, '.') === false) {
            return $this->hasColumn($componentId);
        } else {
            list($columnName, $componentName) = explode('.', $componentId);

            if (!$this->hasColumn($columnName)) {
                return false;
            }

            return $this->getColumn($columnName)->hasComponent($componentName);
        }
    }

    /**
     * @inheritdoc
     */
    final public function getComponent(string $componentId) : IColumnComponent
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