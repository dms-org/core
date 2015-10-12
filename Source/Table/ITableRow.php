<?php

namespace Iddigital\Cms\Core\Table;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;

/**
 * The table row interface
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface ITableRow extends \ArrayAccess
{
    /**
     * Gets the row data as an associative array.
     *
     * @return array
     */
    public function getData();

    /**
     * @param IColumn|string $column
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public function getCellData($column);

    /**
     * @param IColumn|string               $column
     * @param IColumnComponent|string|null $component
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getCellComponentData($column, $component = null);
}