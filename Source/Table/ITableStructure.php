<?php

namespace Iddigital\Cms\Core\Table;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;

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
    public function getColumns();

    /**
     * @return string[]
     */
    public function getColumnNames();

    /**
     * @param string|null $name
     *
     * @return bool
     */
    public function hasColumn($name);

    /**
     * @param string $name
     *
     * @return IColumn
     * @throws InvalidArgumentException
     */
    public function getColumn($name);

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
    public function getColumnAndComponent($componentId);

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