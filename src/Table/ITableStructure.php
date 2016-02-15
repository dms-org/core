<?php declare(strict_types = 1);

namespace Dms\Core\Table;

use Dms\Core\Exception\InvalidArgumentException;

/**
 * The data table structure interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface ITableStructure
{
    /**
     * @return IColumn[]
     */
    public function getColumns() : array;

    /**
     * @return string[]
     */
    public function getColumnNames() : array;

    /**
     * @param string|null $name
     *
     * @return bool
     */
    public function hasColumn($name) : bool;

    /**
     * @param string $name
     *
     * @return IColumn
     * @throws InvalidArgumentException
     */
    public function getColumn(string $name) : IColumn;

    /**
     * Gets the column and component for the given component id.
     *
     * Example:
     * <code>
     * list($column, $component) = $structure->getColumnAndComponent('column_name.component_name')
     * </code>
     *
     * Or for columns with only one component:
     * <code>
     * list($column, $component) = $structure->getColumnAndComponent('column_name')
     * </code>
     *
     * @param string $componentId
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public function getColumnAndComponent(string $componentId) : array;

    /**
     * Gets the column component for the given component id.
     *
     * @param string $componentId
     *
     * @return IColumnComponent
     * @throws InvalidArgumentException
     */
    public function getComponent(string $componentId) : IColumnComponent;

    /**
     * Returns a new table structure with only the supplied columns.
     *
     * @param IColumn[] $columns
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function withColumns(array $columns);
}