<?php

namespace Iddigital\Cms\Core\Table\Data;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Table\IColumn;
use Iddigital\Cms\Core\Table\IColumnComponent;
use Iddigital\Cms\Core\Table\ITableRow;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The table row class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableRow implements ITableRow
{
    /**
     * @var array[]
     */
    protected $data;

    /**
     * TableRow constructor.
     *
     * @param array[] $data
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $data)
    {
        foreach ($data as $columnData) {
            if (!is_array($columnData)) {
                throw InvalidArgumentException::format('Data must only contain arrays, %s found', Debug::getType($columnData));
            }
        }

        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function getCellData(IColumn $column)
    {
        $columnName = $column->getName();

        if (!array_key_exists($columnName, $this->data)) {
            throw InvalidArgumentException::format(
                    'Invalid column supplied to row: expecting one of (%s), %s given',
                    Debug::formatValues(array_keys($this->data)), $columnName
            );
        }

        return $this->data[$columnName];
    }

    /**
     * @param IColumn          $column
     * @param IColumnComponent $component
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getCellComponentData(IColumn $column, IColumnComponent $component)
    {
        $cellData = $this->getCellData($column);

        $componentName = $component->getName();

        if (!isset($cellData[$componentName]) && !array_key_exists($componentName, $cellData)) {
            throw InvalidArgumentException::format(
                    'Invalid column component supplied to row: expecting one of (%s), %s given',
                    Debug::formatValues(array_keys($cellData)), $componentName
            );
        }

        return $cellData[$componentName];
    }
}