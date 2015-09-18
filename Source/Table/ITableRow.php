<?php

namespace Iddigital\Cms\Core\Table;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;

/**
 * The table row interface
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface ITableRow
{
    /**
     * Gets the row data as an associative array.
     *
     * @return array
     */
    public function getData();

    /**
     * @param IColumn $column
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public function getCellData(IColumn $column);

    /**
     * @param IColumn          $column
     * @param IColumnComponent $component
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getCellComponentData(IColumn $column, IColumnComponent $component);
}